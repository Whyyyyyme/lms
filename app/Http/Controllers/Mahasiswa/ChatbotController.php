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
    public function index(Request $request): View
    {
        $this->ensureStudent($request);

        return view('student.chatbot.index');
    }

    /**
     * Fallback non-Livewire agar route student.chatbot.send tidak error.
     * UI utama tetap memakai Livewire ChatbotWidget.
     */
    public function send(Request $request, GeminiChatbotService $chatbot): RedirectResponse
    {
        $this->ensureStudent($request);

        $validated = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:2000'],
        ]);

        $user = $request->user();
        $question = trim($validated['message']);

        /*
         * Simpan pertanyaan hanya untuk akun mahasiswa yang sedang login.
         * Riwayat ini hanya untuk tampilan, bukan untuk sumber fakta AI.
         * GeminiChatbotService yang sudah diperbaiki tidak lagi mengirim ChatHistory lama ke Gemini.
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

        return back()->with('chatbot_answer', $answer);
    }

    /**
     * Hapus riwayat chatbot hanya milik akun yang sedang login.
     * Ini penting setelah sebelumnya AI sempat menampilkan data dari kelas lain.
     */
    public function clear(Request $request): RedirectResponse
    {
        $this->ensureStudent($request);

        ChatHistory::query()
            ->where('user_id', $request->user()->id)
            ->delete();

        return back()->with('success', 'Riwayat chatbot berhasil dihapus.');
    }

    private function ensureStudent(Request $request): void
    {
        $user = $request->user();

        abort_if(! $user, 403);

        /*
         * Mendukung dua kemungkinan:
         * 1. role dari Spatie: hasRole('mahasiswa')
         * 2. kolom biasa users.role = mahasiswa
         */
        $isStudentBySpatie = method_exists($user, 'hasRole') && $user->hasRole('mahasiswa');
        $isStudentByColumn = $user->getAttribute('role') === 'mahasiswa';

        abort_unless($isStudentBySpatie || $isStudentByColumn, 403);
    }
}