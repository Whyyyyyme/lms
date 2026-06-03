<?php

namespace App\Services\Ai;

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

            if (blank($text)) {
                return 'Maaf, Gemini tidak mengembalikan jawaban. Coba ulangi pertanyaan dengan lebih spesifik.';
            }

            return $this->cleanAssistantText($text);
        } catch (Throwable $exception) {
            report($exception);

            return 'Maaf, terjadi kesalahan saat menghubungi Gemini. Periksa koneksi internet, API key, dan konfigurasi model.';
        }
    }

    private function buildPayload(User $user, string $message): array
    {
        return [
            'systemInstruction' => [
                'parts' => [
                    [
                        'text' => $this->contextBuilder->toSystemPrompt($user),
                    ],
                ],
            ],

            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => $this->buildUserPrompt($message),
                        ],
                    ],
                ],
            ],

            'generationConfig' => [
                'temperature' => (float) config('lms-ai.temperature', 0.2),
                'maxOutputTokens' => (int) config('lms-ai.max_output_tokens', 1500),
            ],
        ];
    }

    private function buildUserPrompt(string $message): string
    {
        return "Pertanyaan mahasiswa:\n"
            . trim($message)
            . "\n\n"
            . "Ingat aturan wajib:\n"
            . "- Jawab hanya berdasarkan KONTEKS LMS akun mahasiswa login.\n"
            . "- Jangan menyebut tugas, materi, kelas, semester, nilai, deadline, atau status pengumpulan yang tidak ada di konteks.\n"
            . "- Jika data yang ditanyakan tidak ada di konteks akun ini, jawab bahwa data tersebut belum tersedia untuk akun ini di LMS.\n"
            . "- Jangan menampilkan data dari kelas lain.\n"
            . "- Jangan menebak isi file jika isi_file_tugas atau isi_file_materi kosong.\n\n"

            . "ATURAN FORMAT JAWABAN:\n"
            . "1. Gunakan teks polos yang rapi.\n"
            . "2. Jangan gunakan format Markdown.\n"
            . "3. Jangan gunakan tanda bintang seperti **teks**, *teks*, atau bullet *.\n"
            . "4. Jangan gunakan tanda pagar seperti #, ##, atau ###.\n"
            . "5. Jika membuat daftar, gunakan angka seperti 1., 2., 3. atau gunakan tanda strip - saja.\n"
            . "6. Untuk label informasi, gunakan format biasa seperti Nama tugas: Quiz ke 2.\n\n"

            . "ATURAN KHUSUS UNTUK PERTANYAAN JUMLAH SOAL:\n"
            . "1. Jika mahasiswa hanya bertanya jumlah soal, jawab singkat saja.\n"
            . "2. Format jawaban cukup seperti: Berdasarkan file tugas tersebut, terdapat 3 soal.\n"
            . "3. Jangan langsung menuliskan semua isi soal jika mahasiswa hanya bertanya jumlah soal.\n"
            . "4. Setelah menjawab jumlah soal, boleh tambahkan 1 kalimat: Jika kamu ingin, saya bisa bantu jelaskan isi setiap soal.\n\n"

            . "ATURAN KHUSUS UNTUK RINCIAN SOAL:\n"
            . "1. Jika mahasiswa meminta isi soal, rincian soal, atau penjelasan setiap soal, baru tampilkan daftar soal.\n"
            . "2. Jika daftar soal panjang, ringkas setiap soal agar jawaban tidak terlalu panjang.\n"
            . "3. Jangan memotong jawaban di tengah kalimat.\n\n"

            . "ATURAN UNTUK TUGAS:\n"
            . "1. Jika mahasiswa bertanya apakah ada tugas untuk suatu mata kuliah, jawab dengan daftar tugas yang relevan secara lengkap.\n"
            . "2. Untuk setiap tugas yang relevan, tampilkan minimal nama tugas, mata kuliah, kelas, deadline, status pengumpulan, deskripsi singkat, dan status file tugas.\n"
            . "3. Jika hanya ada 1 tugas, tetap tampilkan detailnya, jangan hanya menyebut nama tugas.\n\n"

            . "ATURAN UNTUK FILE:\n"
            . "1. Jika mahasiswa bertanya jumlah soal, ketentuan, format jawaban, atau isi file tugas, jawab hanya berdasarkan isi_file_tugas.\n"
            . "2. Jika file tersedia tetapi isi file belum bisa dibaca, jelaskan bahwa file ada tetapi isi file belum berhasil dibaca oleh AI.\n"
            . "3. Jika mahasiswa meminta bantuan memahami tugas, jelaskan maksud tugas berdasarkan deskripsi dan isi_file_tugas yang tersedia.\n";
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

    /**
     * Membersihkan jawaban Gemini dari Markdown agar tampilan chatbot lebih rapi.
     */
    private function cleanAssistantText(?string $text): string
    {
        $text = trim((string) $text);

        if ($text === '') {
            return '';
        }

        $text = str_replace(["\r\n", "\r"], "\n", $text);

        /*
         * Ubah markdown link [teks](url) menjadi teks (url).
         */
        $text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '$1 ($2)', $text);

        /*
         * Hapus format bold/italic markdown.
         */
        $text = preg_replace('/\*\*(.*?)\*\*/s', '$1', $text);
        $text = preg_replace('/__(.*?)__/s', '$1', $text);

        /*
         * Hapus heading markdown seperti # Judul.
         */
        $text = preg_replace('/^\s*#{1,6}\s*/m', '', $text);

        /*
         * Ubah bullet markdown bintang menjadi strip biasa.
         */
        $text = preg_replace('/^\s*\*\s+/m', '- ', $text);

        /*
         * Hapus sisa tanda markdown yang sering muncul.
         */
        $text = str_replace(['**', '*', '`'], '', $text);

        /*
         * Rapikan spasi dan baris kosong.
         */
        $text = preg_replace("/[ \t]+\n/", "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);
        $text = preg_replace('/[ \t]{2,}/', ' ', $text);

        return trim((string) $text);
    }

    private function fallbackReply(): string
    {
        if (! config('lms-ai.fallback_enabled', true)) {
            return 'Fitur AI sedang dinonaktifkan.';
        }

        return 'Fitur AI sedang belum aktif. Pastikan LMS_AI_ENABLED=true dan GEMINI_API_KEY sudah diisi di file .env.';
    }
}