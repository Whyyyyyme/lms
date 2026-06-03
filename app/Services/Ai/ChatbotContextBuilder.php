<?php

namespace App\Services\Ai;

use App\Models\Assignment;
use App\Models\Material;
use App\Models\User;
use App\Services\StudentAccessService;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ChatbotContextBuilder
{
    public function build(User $user): array
    {
        $classes = app(StudentAccessService::class)->classesForStudent($user);

        $classIds = $classes
            ->pluck('id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $classesById = $classes->keyBy('id');

        if ($classIds->isEmpty()) {
            return [
                'aturan_akses' => [
                    'catatan' => 'Mahasiswa ini belum memiliki kelas yang boleh diakses. Jangan menyebut materi, tugas, nilai, file, atau deadline apa pun.',
                ],

                'mahasiswa_login' => $this->studentIdentity($user),

                'kelas_diikuti' => [],

                'materi_diizinkan' => [],

                'tugas_diizinkan' => [],
            ];
        }

        $materials = Material::query()
            ->published()
            ->whereIn('class_id', $classIds->all())
            ->latest('published_at')
            ->latest('created_at')
            ->limit((int) config('lms-ai.max_context_materials', 20))
            ->get()
            ->map(function (Material $material) use ($classesById): array {
                $class = $classesById->get($material->class_id);
                $extractedText = $this->cleanLimitedText($material->extracted_text, 6000);

                return [
                    'judul' => $material->title,
                    'deskripsi' => $this->cleanLimitedText($material->description, 1000),
                    'tipe' => $material->type,
                    'dipublikasikan' => optional($material->published_at)->format('d M Y H:i'),

                    /*
                     * Ini yang membuat AI bisa membaca isi file materi.
                     * Jika kosong, berarti file belum ada atau belum berhasil diekstrak.
                     */
                    'status_file_materi' => filled($material->file_path)
                        ? (filled($extractedText) ? 'file tersedia dan isi file berhasil dibaca' : 'file tersedia tetapi isi file belum bisa dibaca')
                        : 'tidak ada file materi',

                    'isi_file_materi' => $extractedText,

                    'kelas_asal' => $this->classContext($class),
                ];
            })
            ->values()
            ->all();

        $assignments = Assignment::query()
            ->published()
            ->whereIn('class_id', $classIds->all())
            ->with([
                'submissions' => fn ($query) => $query->where('student_id', $user->id),
            ])
            ->orderBy('deadline')
            ->latest('created_at')
            ->limit((int) config('lms-ai.max_context_assignments', 30))
            ->get()
            ->map(function (Assignment $assignment) use ($classesById): array {
                $class = $classesById->get($assignment->class_id);
                $submission = $assignment->submissions->first();
                $extractedText = $this->cleanLimitedText($assignment->extracted_text, 8000);

                return [
                    'judul' => $assignment->title,

                    /*
                     * Deskripsi tugas untuk memahami instruksi singkat dari asisten.
                     */
                    'deskripsi' => $this->cleanLimitedText($assignment->description, 1500),

                    /*
                     * Ini yang membuat AI bisa membaca isi file tugas/quiz.
                     * Contoh: jumlah soal, ketentuan, format jawaban, instruksi PDF/DOCX.
                     */
                    'status_file_tugas' => filled($assignment->file_path)
                        ? (filled($extractedText) ? 'file tersedia dan isi file berhasil dibaca' : 'file tersedia tetapi isi file belum bisa dibaca')
                        : 'tidak ada file tugas',

                    'isi_file_tugas' => $extractedText,

                    'deadline' => optional($assignment->deadline)->format('d M Y H:i'),
                    'nilai_maksimal' => $assignment->max_score,

                    'status_pengumpulan' => $submission ? 'sudah dikumpulkan' : 'belum dikumpulkan',
                    'dikumpulkan_pada' => $submission?->submitted_at?->format('d M Y H:i'),
                    'nilai' => $submission?->score,
                    'feedback' => $submission?->feedback,

                    'kelas_asal' => $this->classContext($class),
                ];
            })
            ->values()
            ->all();

        return [
            'aturan_akses' => [
                'sumber_data' => 'Data ini sudah difilter berdasarkan akun mahasiswa yang sedang login.',
                'larangan' => [
                    'Jangan menyebut kelas yang tidak ada di kelas_diikuti.',
                    'Jangan menyebut tugas yang tidak ada di tugas_diizinkan.',
                    'Jangan menyebut materi yang tidak ada di materi_diizinkan.',
                    'Jangan mengambil data dari mahasiswa, kelas, rombel, semester, atau mata kuliah lain.',
                    'Jangan mengarang deadline, nilai, status pengumpulan, materi, tugas, atau isi file.',
                    'Jangan mengaku membaca file jika isi_file_tugas atau isi_file_materi kosong.',
                ],
                'jika_data_tidak_ada' => 'Jawab bahwa data tersebut belum tersedia untuk akun mahasiswa ini di LMS.',
            ],

            'mahasiswa_login' => $this->studentIdentity($user),

            'kelas_diikuti' => $classes
                ->map(fn ($class): array => $this->classContext($class))
                ->values()
                ->all(),

            'materi_diizinkan' => $materials,

            'tugas_diizinkan' => $assignments,
        ];
    }

    public function toSystemPrompt(User $user): string
    {
        $context = $this->build($user);

        return "Kamu adalah AI chatbot LMS Praktikum untuk mahasiswa.\n"
            . "Jawab dalam Bahasa Indonesia yang jelas, sopan, dan ringkas.\n\n"

            . "ATURAN WAJIB:\n"
            . "1. Kamu hanya boleh menggunakan data yang ada di KONTEKS LMS akun mahasiswa yang sedang login.\n"
            . "2. Jangan pernah menyebut tugas, materi, nilai, deadline, kelas, rombel, semester, mata kuliah, atau isi file yang tidak ada di KONTEKS LMS.\n"
            . "3. Jika mahasiswa bertanya tentang tugas/materi/mata kuliah yang tidak ada di konteks akun ini, jawab bahwa data tersebut belum tersedia untuk akun ini di LMS.\n"
            . "4. Jangan mengambil informasi dari kelas lain walaupun nama mata kuliahnya sama.\n"
            . "5. Jangan mengambil informasi dari semester lain walaupun topiknya mirip.\n"
            . "6. Jika mahasiswa mengikuti kelas gabungan, kamu hanya boleh menjawab dari kelas gabungan yang memang muncul di daftar kelas_diikuti.\n"
            . "7. Kamu boleh membantu menjelaskan maksud tugas, cara memahami instruksi, atau langkah pengerjaan berdasarkan deskripsi dan isi_file_tugas yang tersedia di tugas_diizinkan.\n"
            . "8. Kamu boleh membantu menjelaskan materi berdasarkan deskripsi dan isi_file_materi yang tersedia di materi_diizinkan.\n"
            . "9. Jika mahasiswa bertanya jumlah soal, ketentuan, format jawaban, atau isi file tugas, jawab hanya berdasarkan isi_file_tugas.\n"
            . "10. Jika file tersedia tetapi isi file belum bisa dibaca, jelaskan bahwa file ada di LMS tetapi isi file belum berhasil dibaca oleh AI. Jangan menebak isi file.\n"
            . "11. Jika data LMS tidak cukup, katakan bahwa informasi di LMS belum cukup, lalu berikan saran umum tanpa mengarang data LMS.\n"
            . "12. Jika mahasiswa meminta referensi dari blog, artikel, jurnal, atau internet, jelaskan bahwa kamu belum bisa membuka internet langsung dari LMS.\n"
            . "13. Untuk permintaan referensi internet, kamu boleh memberi kata kunci pencarian, struktur pembahasan, dan saran umum yang relevan dengan tugas/materi yang ada di konteks.\n"
            . "14. Jangan mengaku sudah membaca blog, artikel, jurnal, website, atau file tertentu jika data tersebut tidak ada di konteks LMS.\n"
            . "15. Jika pertanyaan benar-benar di luar LMS Praktikum, arahkan mahasiswa untuk bertanya seputar kelas, materi, tugas, deadline, nilai, feedback, atau penjelasan tugas.\n\n"

            . "FORMAT JAWABAN:\n"
            . "- Jika pertanyaan tentang data LMS, jawab berdasarkan konteks saja.\n"
            . "- Jika mahasiswa bertanya apakah ada tugas untuk suatu mata kuliah, tampilkan daftar tugas yang relevan secara lengkap.\n"
            . "- Untuk setiap tugas, tampilkan: nama tugas, mata kuliah, kelas, deadline, status pengumpulan, deskripsi singkat, dan status file tugas.\n"
            . "- Jika hanya ada 1 tugas, tetap tampilkan detailnya, jangan hanya menyebut nama tugas.\n"
            . "- Jika mahasiswa bertanya jumlah soal, ketentuan, format jawaban, atau isi file tugas, jawab berdasarkan isi_file_tugas.\n"
            . "- Jika isi_file_tugas kosong, jangan menebak isi file. Katakan bahwa file tersedia tetapi isi file belum berhasil dibaca oleh AI.\n"
            . "- Jika tidak ada data yang cocok, jangan tampilkan daftar tugas/materi lain.\n"
            . "- Jika mahasiswa bertanya isi file tugas/materi, sebutkan bahwa jawaban berdasarkan file yang tersedia di LMS jika isi_file_tugas atau isi_file_materi memang ada.\n"
            . "- Jangan membocorkan bahwa kamu memiliki batasan sistem, cukup jawab natural sebagai chatbot LMS.\n\n"

            . "KONTEKS LMS AKUN MAHASISWA LOGIN:\n"
            . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function studentIdentity(User $user): array
    {
        return [
            'id' => $user->id,
            'nama' => $user->name,
            'nim_nip' => $user->nim_nip,
            'email' => $user->email,
            'semester' => $user->studySemester?->name,
            'study_semester_id' => $user->study_semester_id,
            'kelas_rombel' => $this->studentGroup($user),
        ];
    }

    private function classContext($class): array
    {
        if (! $class) {
            return [
                'nama_kelas' => null,
                'ruang' => null,
                'jadwal' => null,
                'matakuliah' => null,
                'kode_matakuliah' => null,
                'semester' => null,
                'tipe_kelas' => null,
                'rombel_kelas' => null,
                'anggota_kelas_gabungan' => [],
            ];
        }

        return [
            'id' => $class->id,
            'nama_kelas' => $class->name,
            'ruang' => $class->room,
            'jadwal' => $class->schedule,

            'matakuliah' => $class->course?->name,
            'kode_matakuliah' => $class->course?->code,
            'semester' => $class->course?->studySemester?->name,

            'tipe_kelas' => $class->class_type,
            'rombel_kelas' => $this->classGroup($class),
            'anggota_kelas_gabungan' => $this->classGroupMembers($class),

            'asisten' => $class->assistant?->name,
        ];
    }

    private function studentGroup(User $user): ?string
    {
        return $this->normalizeGroup(
            $user->student_group
                ?? $user->class_group
                ?? $user->group
                ?? $user->rombel
                ?? $user->kelas
                ?? null
        );
    }

    private function classGroup($class): ?string
    {
        return $this->normalizeGroup(
            $class->student_group
                ?? $class->class_group
                ?? $class->group
                ?? $class->rombel
                ?? $class->kelas
                ?? null
        );
    }

    private function classGroupMembers($class): array
    {
        $value = $class->group_members
            ?? $class->student_groups
            ?? $class->groups
            ?? $class->combined_groups
            ?? null;

        return $this->normalizeGroupList($value);
    }

    private function normalizeGroup(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Collection) {
            return null;
        }

        if (is_array($value) || is_object($value)) {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        $value = strtoupper($value);

        $value = preg_replace('/\b(KELAS|ROMBEL|GROUP|GRUP|GABUNGAN|COMBINED)\b/u', '', $value);
        $value = trim((string) $value);
        $value = trim($value, " \t\n\r\0\x0B:-_");

        return $value !== '' ? $value : null;
    }

    private function normalizeGroupList(mixed $value): array
    {
        if ($value === null) {
            return [];
        }

        if ($value instanceof Collection) {
            $value = $value->all();
        }

        if (is_string($value)) {
            $value = trim($value);

            if ($value === '') {
                return [];
            }

            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $value = $decoded;
            } else {
                $value = preg_split('/[,;\/|&\s]+/', $value) ?: [];
            }
        }

        if (! is_array($value)) {
            $value = [$value];
        }

        return collect($value)
            ->map(fn ($item) => $this->normalizeGroup($item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private function cleanLimitedText(mixed $text, int $limit): ?string
    {
        if (blank($text)) {
            return null;
        }

        $text = strip_tags((string) $text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t]+/", ' ', $text);
        $text = preg_replace("/\n{3,}/", "\n\n", (string) $text);
        $text = trim((string) $text);

        if ($text === '') {
            return null;
        }

        return Str::limit($text, $limit);
    }
}