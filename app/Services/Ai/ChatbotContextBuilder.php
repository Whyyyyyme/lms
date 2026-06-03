<?php

namespace App\Services\Ai;

use App\Models\Assignment;
use App\Models\Material;
use App\Models\User;
use App\Services\StudentAccessService;
use Illuminate\Support\Str;

class ChatbotContextBuilder
{
    public function build(User $user): array
    {
        $classes = app(StudentAccessService::class)->classesForStudent($user);

        $classIds = $classes
            ->pluck('id')
            ->filter()
            ->values();

        $materials = Material::query()
            ->published()
            ->whereIn('class_id', $classIds)
            ->latest('published_at')
            ->latest('created_at')
            ->limit((int) config('lms-ai.max_context_materials', 8))
            ->get()
            ->map(fn (Material $material): array => [
                'judul' => $material->title,
                'deskripsi' => Str::limit(strip_tags((string) $material->description), 700),
                'tipe' => $material->type,
                'dipublikasikan' => optional($material->published_at)->format('d M Y H:i'),
            ])
            ->values()
            ->all();

        $assignments = Assignment::query()
            ->published()
            ->whereIn('class_id', $classIds)
            ->with([
                'submissions' => fn ($query) => $query->where('student_id', $user->id),
            ])
            ->orderBy('deadline')
            ->limit((int) config('lms-ai.max_context_assignments', 10))
            ->get()
            ->map(function (Assignment $assignment): array {
                $submission = $assignment->submissions->first();

                return [
                    'judul' => $assignment->title,
                    'deskripsi' => Str::limit(strip_tags((string) $assignment->description), 500),
                    'deadline' => optional($assignment->deadline)->format('d M Y H:i'),
                    'nilai_maksimal' => $assignment->max_score,
                    'status' => $submission ? 'sudah dikumpulkan' : 'belum dikumpulkan',
                    'dikumpulkan_pada' => $submission?->submitted_at?->format('d M Y H:i'),
                    'nilai' => $submission?->score,
                    'feedback' => $submission?->feedback,
                ];
            })
            ->values()
            ->all();

        return [
            'user' => [
                'nama' => $user->name,
                'nim_nip' => $user->nim_nip,
                'email' => $user->email,
            ],

            'kelas' => $classes
                ->map(fn ($class): array => [
                    'nama' => $class->name,
                    'ruang' => $class->room,
                    'jadwal' => $class->schedule,
                    'matakuliah' => $class->course?->name,
                    'kode_matakuliah' => $class->course?->code,
                ])
                ->values()
                ->all(),

            'materi' => $materials,
            'tugas' => $assignments,
        ];
    }

    public function toSystemPrompt(User $user): string
    {
        $context = $this->build($user);

        return "Kamu adalah AI chatbot LMS Praktikum untuk mahasiswa. "
            . "Jawab dalam Bahasa Indonesia yang jelas, sopan, dan ringkas. "
            . "Gunakan konteks data LMS berikut untuk menjawab pertanyaan mahasiswa. "
            . "Jika pertanyaan di luar konteks LMS, tetap bantu secara umum tetapi jelaskan bila data LMS tidak tersedia. "
            . "Jangan mengarang nilai, deadline, materi, tugas, atau status submission yang tidak ada di konteks. "
            . "Jika mahasiswa menanyakan tugas yang belum muncul atau belum dipublikasikan, jawab bahwa data tugas tersebut belum tersedia di LMS.\n\n"
            . "KONTEKS LMS:\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}