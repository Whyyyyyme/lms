<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\ChatHistory;
use App\Services\Ai\GeminiChatbotService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChatbotController extends Controller
{
    public function index(): View
    {
        return view('student.chatbot.index');
    }

    /**
     * Fallback non-Livewire agar route student.chatbot.send tidak error.
     * UI utama tetap memakai Livewire ChatbotWidget.
     */
    public function send(Request $request, GeminiChatbotService $chatbot): RedirectResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $user = $request->user();
        $question = trim($validated['message']);

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

        return back()->with('chatbot_answer', $answer);
    }
}
