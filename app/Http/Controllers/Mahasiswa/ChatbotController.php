<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\ChatHistory;
use App\Models\Material;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class ChatbotController extends Controller
{
    use ResolvesClassAccess;

    public function index(): View
    {
        $histories = ChatHistory::where('user_id', auth()->id())
            ->latest()
            ->limit(20)
            ->get()
            ->reverse()
            ->values();

        return view('student.chatbot.index', compact('histories'));
    }

    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);

        ChatHistory::create([
            'user_id' => auth()->id(),
            'role' => 'user',
            'message' => $validated['message'],
        ]);

        $answer = $this->askOpenAi($validated['message']);

        ChatHistory::create([
            'user_id' => auth()->id(),
            'role' => 'assistant',
            'message' => $answer,
        ]);

        return response()->json([
            'message' => $answer,
        ]);
    }

    private function askOpenAi(string $message): string
    {
        if (! class_exists(\OpenAI\Laravel\Facades\OpenAI::class)) {
            return 'Fitur AI belum aktif karena package OpenAI belum tersedia. Pertanyaan kamu sudah tersimpan di riwayat chat.';
        }

        try {
            $response = \OpenAI\Laravel\Facades\OpenAI::chat()->create([
                'model' => config('openai.chat_model', env('OPENAI_MODEL', 'gpt-4o-mini')),
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => 0.3,
            ]);

            return trim($response->choices[0]->message->content ?? 'Maaf, AI belum bisa memberi jawaban saat ini.');
        } catch (Throwable $exception) {
            report($exception);

            return 'Maaf, layanan AI sedang belum bisa diakses. Coba lagi nanti atau tanyakan langsung ke asisten praktikum.';
        }
    }

    private function systemPrompt(): string
    {
        $user = auth()->user();
        $classIds = $this->studentClassIds($user);

        $materials = Material::published()
            ->whereIn('class_id', $classIds)
            ->latest('published_at')
            ->limit(10)
            ->get(['title', 'description', 'type']);

        $assignments = Assignment::with(['submissions' => fn ($query) => $query->where('student_id', $user->id)])
            ->whereIn('class_id', $classIds)
            ->orderBy('deadline')
            ->get()
            ->map(function (Assignment $assignment) {
                $submission = $assignment->submissions->first();

                return [
                    'title' => $assignment->title,
                    'deadline' => $assignment->deadline?->format('d/m/Y H:i'),
                    'status' => $submission ? 'sudah dikumpulkan' : 'belum dikumpulkan',
                    'score' => $submission?->score,
                ];
            });

        return "Kamu adalah AI chatbot LMS Praktikum. Jawab dalam Bahasa Indonesia yang jelas dan ramah. "
            . "Nama mahasiswa: {$user->name}. "
            . "Gunakan konteks materi dan tugas berikut. Jangan mengarang data di luar konteks. "
            . "Materi: " . $materials->toJson(JSON_UNESCAPED_UNICODE) . ". "
            . "Tugas: " . $assignments->toJson(JSON_UNESCAPED_UNICODE) . ". "
            . "Jika ditanya tugas yang belum dikumpulkan, jawab dengan format nama tugas, deadline, dan status.";
    }
}
