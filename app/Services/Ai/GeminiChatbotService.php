<?php

namespace App\Services\Ai;

use App\Models\ChatHistory;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Throwable;

class GeminiChatbotService
{
    public function __construct(
        private readonly ChatbotContextBuilder $contextBuilder,
    ) {
    }

    public function reply(User $user, string $message): string
    {
        if (! config('lms-ai.enabled', true)) {
            return $this->fallbackReply();
        }

        $apiKey = config('lms-ai.gemini.api_key');

        if (blank($apiKey)) {
            return 'API key Gemini belum diatur. Tambahkan GEMINI_API_KEY di file .env, lalu jalankan php artisan config:clear.';
        }

        try {
            $payload = $this->buildPayload($user, $message);
            $baseUrl = rtrim((string) config('lms-ai.gemini.base_url'), '/');
            $model = config('lms-ai.gemini.model', 'gemini-3.5-flash');

            $response = Http::timeout((int) config('lms-ai.gemini.timeout', 30))
                ->withHeaders([
                    'x-goog-api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])
                ->post("{$baseUrl}/models/{$model}:generateContent", $payload);

            if ($response->failed()) {
                report(new \RuntimeException('Gemini API error: ' . $response->body()));

                return 'Maaf, Gemini sedang tidak bisa dihubungi. Coba lagi nanti atau periksa API key dan model Gemini di .env.';
            }

            $text = $this->extractText($response->json());

            return filled($text)
                ? trim($text)
                : 'Maaf, Gemini tidak mengembalikan jawaban. Coba ulangi pertanyaan dengan lebih spesifik.';
        } catch (Throwable $exception) {
            report($exception);

            return 'Maaf, terjadi kesalahan saat menghubungi Gemini. Periksa koneksi internet, API key, dan konfigurasi model.';
        }
    }

    private function buildPayload(User $user, string $message): array
    {
        $historyLimit = (int) config('lms-ai.max_history_messages', 8);

        $histories = ChatHistory::query()
            ->where('user_id', $user->id)
            ->latest()
            ->limit($historyLimit)
            ->get()
            ->reverse()
            ->values();

        $contents = $histories
            ->map(fn (ChatHistory $history): array => [
                'role' => $history->role === 'assistant' ? 'model' : 'user',
                'parts' => [
                    ['text' => (string) $history->message],
                ],
            ])
            ->all();

        $contents[] = [
            'role' => 'user',
            'parts' => [
                ['text' => $message],
            ],
        ];

        return [
            'systemInstruction' => [
                'parts' => [
                    ['text' => $this->contextBuilder->toSystemPrompt($user)],
                ],
            ],
            'contents' => $contents,
            'generationConfig' => [
                'temperature' => (float) config('lms-ai.temperature', 0.3),
                'maxOutputTokens' => (int) config('lms-ai.max_output_tokens', 900),
            ],
        ];
    }

    private function extractText(array $json): ?string
    {
        $parts = Arr::get($json, 'candidates.0.content.parts', []);

        if (! is_array($parts)) {
            return null;
        }

        return collect($parts)
            ->pluck('text')
            ->filter()
            ->implode("\n");
    }

    private function fallbackReply(): string
    {
        if (! config('lms-ai.fallback_enabled', true)) {
            return 'Fitur AI sedang dinonaktifkan.';
        }

        return 'Fitur AI sedang belum aktif. Pastikan LMS_AI_ENABLED=true dan GEMINI_API_KEY sudah diisi di file .env.';
    }
}
