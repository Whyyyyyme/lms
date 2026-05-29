<?php

namespace App\Livewire;

use App\Models\ChatHistory;
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
        $this->loadHistories();
    }

    public function send(GeminiChatbotService $chatbot): void
    {
        $this->validate([
            'message' => ['required', 'string', 'min:2', 'max:2000'],
        ], [
            'message.required' => 'Pertanyaan wajib diisi.',
            'message.min' => 'Pertanyaan terlalu pendek.',
            'message.max' => 'Pertanyaan maksimal 2000 karakter.',
        ]);

        $user = Auth::user();
        $question = trim($this->message);

        $this->isSending = true;

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
        $this->isSending = false;
        $this->loadHistories();
    }

    public function clearHistory(): void
    {
        ChatHistory::where('user_id', Auth::id())->delete();
        $this->loadHistories();
    }

    public function loadHistories(): void
    {
        $this->histories = ChatHistory::query()
            ->where('user_id', Auth::id())
            ->oldest()
            ->get()
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
}
