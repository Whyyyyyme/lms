<?php

namespace App\Livewire;

use App\Models\ChatHistory;
use App\Models\User;
use App\Services\Ai\GeminiChatbotService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ChatbotWidget extends Component
{
    public string $message = '';

    public array $histories = [];

    public bool $isSending = false;

    public function mount(): void
    {
        $this->ensureStudent();
        $this->loadHistories();
    }

    public function send(GeminiChatbotService $chatbot): void
    {
        $this->ensureStudent();

        $this->message = trim($this->message);

        $this->validate([
            'message' => ['required', 'string', 'min:2', 'max:2000'],
        ], [
            'message.required' => 'Pertanyaan wajib diisi.',
            'message.min' => 'Pertanyaan terlalu pendek.',
            'message.max' => 'Pertanyaan maksimal 2000 karakter.',
        ]);

        $user = Auth::user();

        if (! $user instanceof User) {
            abort(403);
        }

        $question = $this->message;

        $this->isSending = true;

        try {
            /*
             * Riwayat ini hanya untuk tampilan chat mahasiswa.
             * GeminiChatbotService yang sudah diperbaiki tidak memakai ChatHistory lama
             * sebagai sumber fakta, agar jawaban lama yang salah tidak terbawa lagi.
             */
            ChatHistory::create([
                'user_id' => $user->id,
                'role' => 'user',
                'message' => $question,
            ]);

            $answer = $chatbot->reply($user, $question);

            ChatHistory::create([
                'user_id' => $user->id,
                'role' => 'assistant',
                'message' => $answer,
            ]);

            $this->message = '';
            $this->loadHistories();
        } finally {
            $this->isSending = false;
        }
    }

    public function clearHistory(): void
    {
        $this->ensureStudent();

        ChatHistory::query()
            ->where('user_id', Auth::id())
            ->delete();

        $this->histories = [];

        session()->flash('success', 'Riwayat chatbot berhasil dihapus.');
    }

    public function loadHistories(): void
    {
        $userId = Auth::id();

        if (! $userId) {
            $this->histories = [];

            return;
        }

        /*
         * Ambil maksimal 60 pesan terakhir agar halaman tidak berat.
         * Setelah itu dibalik lagi supaya tampil dari pesan lama ke baru.
         */
        $this->histories = ChatHistory::query()
            ->where('user_id', $userId)
            ->latest()
            ->limit(60)
            ->get()
            ->reverse()
            ->values()
            ->map(fn (ChatHistory $history): array => [
                'role' => $history->role,
                'message' => $history->message,
                'time' => $history->created_at?->format('H:i'),
            ])
            ->all();
    }

    public function render()
    {
        return view('livewire.chatbot-widget');
    }

    private function ensureStudent(): void
    {
        $user = Auth::user();

        abort_if(! $user, 403);

        /*
         * Mendukung 2 sistem role:
         * 1. Spatie Permission: hasRole('mahasiswa')
         * 2. Kolom biasa: users.role = mahasiswa
         */
        $isStudentBySpatie = method_exists($user, 'hasRole') && $user->hasRole('mahasiswa');
        $isStudentByColumn = $user->getAttribute('role') === 'mahasiswa';

        abort_unless($isStudentBySpatie || $isStudentByColumn, 403);
    }
}