<?php

namespace App\Livewire;

use App\Models\Assignment;
use App\Models\ChatHistory;
use App\Models\Material;
use App\Models\PraktikumClass;
use Illuminate\Support\Collection;
use Livewire\Component;
use Throwable;

class ChatbotWidget extends Component
{
    public string $message = '';

    public array $histories = [];

    public bool $isSending = false;

    public ?string $errorMessage = null;

    public int $limit = 30;

    public function mount(): void
    {
        $this->loadHistories();
    }

    public function loadHistories(): void
    {
        $this->histories = ChatHistory::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->limit($this->limit)
            ->get()
            ->reverse()
            ->map(fn (ChatHistory $history): array => [
                'id' => $history->id,
                'role' => $history->role,
                'message' => $history->message,
                'created_at' => $history->created_at?->format('H:i'),
            ])
            ->values()
            ->all();
    }

    public function sendMessage(): void
    {
        $this->validate([
            'message' => ['required', 'string', 'min:2', 'max:4000'],
        ], [
            'message.required' => 'Pesan tidak boleh kosong.',
            'message.min' => 'Pesan terlalu pendek.',
            'message.max' => 'Pesan maksimal 4000 karakter.',
        ]);

        $userMessage = trim($this->message);
        $this->message = '';
        $this->errorMessage = null;
        $this->isSending = true;

        ChatHistory::create([
            'user_id' => auth()->id(),
            'role' => 'user',
            'message' => $userMessage,
        ]);

        $this->loadHistories();

        try {
            $answer = $this->askOpenAi($userMessage);
        } catch (Throwable $exception) {
            report($exception);
            $answer = 'Maaf, layanan AI sedang belum bisa diakses. Coba lagi nanti atau tanyakan langsung ke asisten praktikum.';
            $this->errorMessage = 'AI sedang bermasalah, tetapi pertanyaan kamu sudah tersimpan.';
        }

        ChatHistory::create([
            'user_id' => auth()->id(),
            'role' => 'assistant',
            'message' => $answer,
        ]);

        $this->isSending = false;
        $this->loadHistories();
        $this->dispatch('chatbot-message-sent');
    }

    public function clearHistory(): void
    {
        ChatHistory::query()
            ->where('user_id', auth()->id())
            ->delete();

        $this->histories = [];
        $this->message = '';
        $this->errorMessage = null;

        $this->dispatch('chatbot-history-cleared');
    }

    private function askOpenAi(string $message): string
    {
        if (! class_exists(\OpenAI\Laravel\Facades\OpenAI::class)) {
            return 'Fitur AI belum aktif karena package OpenAI belum tersedia. Pertanyaan kamu sudah tersimpan di riwayat chat.';
        }

        $recentHistories = ChatHistory::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->limit(8)
            ->get()
            ->reverse()
            ->map(fn (ChatHistory $history): array => [
                'role' => $history->role,
                'content' => $history->message,
            ])
            ->values()
            ->all();

        $messages = array_merge(
            [[
                'role' => 'system',
                'content' => $this->systemPrompt(),
            ]],
            $recentHistories,
            [[
                'role' => 'user',
                'content' => $message,
            ]],
        );

        $response = \OpenAI\Laravel\Facades\OpenAI::chat()->create([
            'model' => config('openai.chat_model', env('OPENAI_MODEL', 'gpt-4o-mini')),
            'messages' => $messages,
            'temperature' => 0.3,
        ]);

        return trim($response->choices[0]->message->content ?? 'Maaf, AI belum bisa memberi jawaban saat ini.');
    }

    private function systemPrompt(): string
    {
        $user = auth()->user();
        $classIds = $this->studentClassIds();

        $materials = Material::query()
            ->published()
            ->whereIn('class_id', $classIds)
            ->latest('published_at')
            ->limit(10)
            ->get(['title', 'description', 'type'])
            ->map(fn (Material $material): array => [
                'judul' => $material->title,
                'ringkasan' => str($material->description ?? '-')->limit(500)->toString(),
                'tipe' => $material->type,
            ]);

        $assignments = Assignment::query()
            ->with(['submissions' => fn ($query) => $query->where('student_id', $user->id)])
            ->whereIn('class_id', $classIds)
            ->orderBy('deadline')
            ->get()
            ->map(function (Assignment $assignment): array {
                $submission = $assignment->submissions->first();

                return [
                    'nama_tugas' => $assignment->title,
                    'deadline' => $assignment->deadline?->format('d/m/Y H:i'),
                    'status' => $submission ? 'sudah dikumpulkan' : 'belum dikumpulkan',
                    'nilai' => $submission?->score,
                    'feedback' => $submission?->feedback,
                ];
            });

        return 'Kamu adalah AI chatbot LMS Praktikum untuk mahasiswa. '
            . 'Jawab selalu dalam Bahasa Indonesia yang jelas, sopan, dan mudah dipahami. '
            . "Nama mahasiswa: {$user->name}. "
            . 'Gunakan konteks materi dan tugas berikut. Jangan mengarang data di luar konteks LMS. '
            . 'Kalau pertanyaan di luar konteks, jawab secara umum dan arahkan mahasiswa bertanya ke asisten jika butuh kepastian. '
            . 'Jika ditanya tugas yang belum dikumpulkan, jawab dengan format: nama tugas, deadline, dan status. '
            . 'Materi: ' . $materials->toJson(JSON_UNESCAPED_UNICODE) . '. '
            . 'Tugas: ' . $assignments->toJson(JSON_UNESCAPED_UNICODE) . '.';
    }

    private function studentClassIds(): array
    {
        $user = auth()->user();

        $ids = collect();

        if ($user?->kelas_id) {
            $ids->push((int) $user->kelas_id);
        }

        if (method_exists($user, 'kelasDiikuti')) {
            $ids = $ids->merge($user->kelasDiikuti()->pluck('classes.id'));
        }

        return $ids->unique()->values()->all();
    }

    public function render()
    {
        return view('livewire.chatbot-widget');
    }
}
