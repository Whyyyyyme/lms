<?php

namespace App\Services;

use App\Models\PraktikumClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Throwable;

class StudentAccessService
{
    /**
     * Ambil ID kelas yang benar-benar boleh diakses mahasiswa.
     *
     * Aturan:
     * - Mahasiswa hanya boleh melihat kelas sesuai semester.
     * - Untuk kelas regular, mahasiswa hanya boleh melihat kelas yang student_group-nya sama.
     * - Untuk kelas gabungan, mahasiswa hanya boleh melihat jika group_members memuat rombel mahasiswa.
     * - Data lama/manual tetap didukung lewat class_students dan users.kelas_id.
     *
     * @return array<int>
     */
    public function classIdsForStudent(?User $student): array
    {
        if (! $student) {
            return [];
        }

        $classIds = collect();

        /**
         * 1. Akses berdasarkan semester + rombel mahasiswa.
         * Ini yang dipakai untuk mencegah mahasiswa melihat semua kelas dalam semester yang sama.
         */
        if ($student->study_semester_id) {
            $semesterClasses = PraktikumClass::query()
                ->active()
                ->with(['course.studySemester', 'course.academicYear', 'assistant'])
                ->whereHas('course', function ($query) use ($student) {
                    $query->where('study_semester_id', $student->study_semester_id)
                        ->where('is_active', true);
                })
                ->get()
                ->filter(fn (PraktikumClass $class) => $this->studentCanAccessClassBySemesterAndGroup($student, $class));

            $classIds = $classIds->merge($semesterClasses->pluck('id'));
        }

        /**
         * 2. Akses manual dari pivot class_students.
         * Ini dipertahankan agar data lama/manual tetap jalan.
         */
        if (method_exists($student, 'kelasDiikuti')) {
            $classIds = $classIds->merge($student->kelasDiikuti()->pluck('classes.id'));
        }

        /**
         * 3. Akses legacy dari users.kelas_id.
         * Ini juga dipertahankan agar data lama tetap kompatibel.
         */
        if ($student->kelas_id) {
            $classIds->push($student->kelas_id);
        }

        return $classIds
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Ambil kelas praktikum yang boleh diakses mahasiswa.
     */
    public function classesForStudent(?User $student): Collection
    {
        $classIds = $this->classIdsForStudent($student);

        if ($classIds === []) {
            return collect();
        }

        return PraktikumClass::query()
            ->with(['course.studySemester', 'course.academicYear', 'assistant'])
            ->whereIn('id', $classIds)
            ->orderBy('name')
            ->get();
    }

    /**
     * Ambil semua mahasiswa aktif yang berhak mengikuti kelas.
     *
     * Aturan ini juga diperketat:
     * - Kelas regular E hanya mengambil mahasiswa semester terkait yang rombelnya E.
     * - Kelas gabungan E/F hanya mengambil mahasiswa yang rombelnya E atau F.
     * - Mahasiswa yang ditambahkan manual lewat class_students atau users.kelas_id tetap ikut.
     */
    public function studentsForClass(?PraktikumClass $class): Collection
    {
        if (! $class) {
            return collect();
        }

        $class->loadMissing(['course.studySemester', 'students.studySemester']);

        $students = collect();

        /**
         * 1. Ambil mahasiswa berdasarkan semester, lalu filter berdasarkan rombel/kelas.
         */
        if ($class->course?->study_semester_id) {
            $semesterStudents = User::query()
                ->role('mahasiswa')
                ->active()
                ->with('studySemester')
                ->where('study_semester_id', $class->course->study_semester_id)
                ->orderBy('name')
                ->get()
                ->filter(fn (User $student) => $this->studentCanAccessClassBySemesterAndGroup($student, $class));

            $students = $students->merge($semesterStudents);
        }

        /**
         * 2. Tambahkan mahasiswa dari relasi manual class_students.
         */
        if ($class->relationLoaded('students')) {
            $students = $students->merge($class->students);
        } else {
            $students = $students->merge(
                $class->students()
                    ->with('studySemester')
                    ->active()
                    ->get()
            );
        }

        /**
         * 3. Tambahkan mahasiswa dari data lama users.kelas_id.
         */
        $legacyStudents = User::query()
            ->role('mahasiswa')
            ->active()
            ->with('studySemester')
            ->where('kelas_id', $class->id)
            ->get();

        return $students
            ->merge($legacyStudents)
            ->filter(fn ($student) => $student instanceof User && $student->is_active)
            ->unique('id')
            ->sortBy('name')
            ->values();
    }

    public function countStudentsForClass(?PraktikumClass $class): int
    {
        return $this->studentsForClass($class)->count();
    }

    /**
     * Tambahkan properti tampilan tanpa menyimpan ke database.
     */
    public function attachResolvedStudentCounts(Collection|EloquentCollection $classes): Collection|EloquentCollection
    {
        $classes->each(function (PraktikumClass $class): void {
            $class->setAttribute('resolved_students_count', $this->countStudentsForClass($class));
        });

        return $classes;
    }

    /**
     * Cek apakah mahasiswa boleh mengakses kelas berdasarkan semester dan rombel.
     *
     * Method ini sengaja tidak mengecek class_students / kelas_id,
     * karena akses manual itu sudah ditambahkan terpisah di classIdsForStudent() dan studentsForClass().
     */
    private function studentCanAccessClassBySemesterAndGroup(User $student, PraktikumClass $class): bool
    {
        $class->loadMissing('course');

        $studentSemesterId = $student->study_semester_id ? (int) $student->study_semester_id : null;
        $classSemesterId = $class->course?->study_semester_id ? (int) $class->course->study_semester_id : null;

        if (! $studentSemesterId || ! $classSemesterId) {
            return false;
        }

        if ($studentSemesterId !== $classSemesterId) {
            return false;
        }

        $studentGroup = $this->studentGroup($student);

        if (! $studentGroup) {
            return false;
        }

        /**
         * Jika kelas punya group_members, anggap sebagai kelas gabungan.
         * Contoh group_members: ["E", "F"].
         */
        $groupMembers = $this->classGroupMembers($class);

        if ($groupMembers !== []) {
            return in_array($studentGroup, $groupMembers, true);
        }

        /**
         * Jika kelas punya student_group, anggap sebagai kelas regular.
         * Contoh student_group: E.
         */
        $classGroup = $this->classGroup($class);

        if ($classGroup) {
            return $studentGroup === $classGroup;
        }

        /**
         * Fallback terakhir:
         * Jika data lama belum punya student_group/group_members,
         * coba baca dari nama kelas seperti "Kelas E" atau "Rombel E".
         */
        return $this->classNameContainsStudentGroup($class, $studentGroup);
    }

    /**
     * Ambil rombel mahasiswa dari beberapa kemungkinan nama kolom.
     */
    private function studentGroup(User $student): ?string
    {
        $possibleFields = [
            'student_group',
            'class_group',
            'group',
            'rombel',
            'kelas',
        ];

        foreach ($possibleFields as $field) {
            $group = $this->normalizeGroup($student->getAttribute($field));

            if ($group) {
                return $group;
            }
        }

        return null;
    }

    /**
     * Ambil rombel regular dari kelas praktikum.
     */
    private function classGroup(PraktikumClass $class): ?string
    {
        $possibleFields = [
            'student_group',
            'class_group',
            'group',
            'rombel',
            'kelas',
        ];

        foreach ($possibleFields as $field) {
            $group = $this->normalizeGroup($class->getAttribute($field));

            if ($group) {
                return $group;
            }
        }

        return null;
    }

    /**
     * Ambil daftar rombel yang tergabung dalam kelas gabungan.
     *
     * Mendukung format:
     * - array: ["E", "F"]
     * - JSON string: ["E","F"]
     * - string biasa: E,F atau E/F atau E F
     */
    private function classGroupMembers(PraktikumClass $class): array
    {
        $possibleFields = [
            'group_members',
            'student_groups',
            'groups',
            'combined_groups',
        ];

        foreach ($possibleFields as $field) {
            $groups = $this->normalizeGroupList($class->getAttribute($field));

            if ($groups !== []) {
                return $groups;
            }
        }

        return [];
    }

    /**
     * Normalisasi satu rombel.
     *
     * Contoh:
     * - "e" menjadi "E"
     * - "Kelas E" menjadi "E"
     * - "Rombel E" menjadi "E"
     */
    private function normalizeGroup(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_array($value)) {
            return null;
        }

        if ($value instanceof Collection) {
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

    /**
     * Normalisasi daftar rombel.
     */
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
            ->flatMap(function ($item) {
                if ($item instanceof Collection) {
                    return $item->all();
                }

                if (is_array($item)) {
                    return collect([
                        $item['student_group'] ?? null,
                        $item['class_group'] ?? null,
                        $item['group'] ?? null,
                        $item['rombel'] ?? null,
                        $item['kelas'] ?? null,
                        $item['name'] ?? null,
                        $item['value'] ?? null,
                    ])->filter()->values()->all();
                }

                if (is_object($item)) {
                    try {
                        $array = (array) $item;

                        return collect([
                            $array['student_group'] ?? null,
                            $array['class_group'] ?? null,
                            $array['group'] ?? null,
                            $array['rombel'] ?? null,
                            $array['kelas'] ?? null,
                            $array['name'] ?? null,
                            $array['value'] ?? null,
                        ])->filter()->values()->all();
                    } catch (Throwable) {
                        return [];
                    }
                }

                return [$item];
            })
            ->map(fn ($item) => $this->normalizeGroup($item))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Fallback untuk data lama yang rombelnya hanya tersimpan di nama kelas.
     */
    private function classNameContainsStudentGroup(PraktikumClass $class, string $studentGroup): bool
    {
        $name = strtoupper((string) $class->getAttribute('name'));

        if ($name === '') {
            return false;
        }

        $group = preg_quote($studentGroup, '/');

        return preg_match('/\bKELAS\s*' . $group . '\b/u', $name) === 1
            || preg_match('/\bROMBEL\s*' . $group . '\b/u', $name) === 1
            || preg_match('/(^|[^A-Z0-9])' . $group . '([^A-Z0-9]|$)/u', $name) === 1;
    }
}