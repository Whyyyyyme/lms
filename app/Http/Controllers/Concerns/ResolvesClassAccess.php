<?php

namespace App\Http\Controllers\Concerns;

use App\Models\PraktikumClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait ResolvesClassAccess
{
    protected function assistantClassesQuery(?User $user = null): Builder
    {
        $user ??= auth()->user();

        return PraktikumClass::query()->where('assistant_id', $user->id);
    }

    protected function assistantClassOrFail(int $classId, ?User $user = null): PraktikumClass
    {
        return $this->assistantClassesQuery($user)->findOrFail($classId);
    }

    /**
     * Mengambil ID kelas yang boleh diakses mahasiswa.
     *
     * Sumber akses mahasiswa:
     * 1. Kelas reguler berdasarkan semester + rombel mahasiswa.
     * 2. Kelas gabungan berdasarkan semester + group_members.
     * 3. Kelas manual/khusus dari tabel class_students.
     *
     * Catatan:
     * users.kelas_id lama tidak lagi dijadikan akses utama agar mahasiswa
     * semester/rombel lama tidak salah masuk ke kelas baru.
     */
    protected function studentClassIds(?User $user = null): array
    {
        $user ??= auth()->user();

        $manualClassIds = $user->kelasDiikuti()
            ->pluck('classes.id')
            ->all();

        $automaticClassIds = [];

        if ($user->study_semester_id && $user->student_group) {
            $studentGroup = strtoupper((string) $user->student_group);

            $automaticClassIds = PraktikumClass::query()
                ->whereHas('course', function ($query) use ($user) {
                    $query->where('study_semester_id', $user->study_semester_id);
                })
                ->where(function ($query) use ($studentGroup) {
                    $query
                        ->where(function ($regularQuery) use ($studentGroup) {
                            $regularQuery
                                ->where('class_type', 'regular')
                                ->where('student_group', $studentGroup);
                        })
                        ->orWhere(function ($combinedQuery) use ($studentGroup) {
                            $combinedQuery
                                ->where('class_type', 'combined')
                                ->whereJsonContains('group_members', $studentGroup);
                        });
                })
                ->pluck('classes.id')
                ->all();
        }

        return array_values(array_unique(array_merge($manualClassIds, $automaticClassIds)));
    }

    protected function studentClasses(?User $user = null): Collection
    {
        $user ??= auth()->user();

        $classIds = $this->studentClassIds($user);

        if (empty($classIds)) {
            return collect();
        }

        return PraktikumClass::query()
            ->with(['course.studySemester', 'assistant'])
            ->whereIn('id', $classIds)
            ->orderBy('name')
            ->get();
    }

    /**
     * Query dasar mahasiswa.
     *
     * Mengecek dua sumber role:
     * 1. users.role = mahasiswa
     * 2. Spatie role = mahasiswa
     *
     * Ini dibuat agar aman untuk data lama yang mungkin belum sinkron.
     */
    protected function studentUsersQuery(bool $activeOnly = true): Builder
    {
        return User::query()
            ->when($activeOnly, function ($query) {
                $query->where('users.is_active', true);
            })
            ->where(function ($query) {
                $query->where('users.role', 'mahasiswa')
                    ->orWhereHas('roles', function ($roleQuery) {
                        $roleQuery->where('name', 'mahasiswa');
                    });
            });
    }

    /**
     * Mengambil semua ID mahasiswa yang berhak mengikuti kelas.
     *
     * Sumber mahasiswa:
     * 1. Mahasiswa otomatis dari semester mata kuliah + rombel kelas.
     * 2. Mahasiswa manual/khusus dari tabel class_students.
     *
     * Untuk kelas reguler:
     * - users.study_semester_id = course.study_semester_id
     * - users.student_group = classes.student_group
     *
     * Untuk kelas gabungan:
     * - users.study_semester_id = course.study_semester_id
     * - users.student_group ada di classes.group_members
     */
    protected function studentIdsForClass(PraktikumClass $class, bool $activeOnly = true): array
    {
        $class->loadMissing('course');

        $automaticStudentIds = $this->automaticStudentIdsForClass($class, $activeOnly);
        $manualStudentIds = $this->manualStudentIdsForClass($class, $activeOnly);

        return collect()
            ->merge($automaticStudentIds)
            ->merge($manualStudentIds)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function studentsForClass(PraktikumClass $class, bool $activeOnly = true): Collection
    {
        $studentIds = $this->studentIdsForClass($class, $activeOnly);

        if (empty($studentIds)) {
            return collect();
        }

        return User::query()
            ->with('studySemester')
            ->whereIn('id', $studentIds)
            ->orderBy('student_group')
            ->orderBy('name')
            ->get();
    }

    protected function studentCountForClass(PraktikumClass $class, bool $activeOnly = true): int
    {
        return count($this->studentIdsForClass($class, $activeOnly));
    }

    protected function automaticStudentIdsForClass(PraktikumClass $class, bool $activeOnly = true): array
    {
        $class->loadMissing('course');

        if (! $class->course?->study_semester_id) {
            return [];
        }

        $query = $this->studentUsersQuery($activeOnly)
            ->where('study_semester_id', $class->course->study_semester_id);

        $classType = $class->class_type ?? 'regular';

        if ($classType === 'regular') {
            if (! $class->student_group) {
                return [];
            }

            return $query
                ->where('student_group', strtoupper((string) $class->student_group))
                ->pluck('users.id')
                ->all();
        }

        if ($classType === 'combined') {
            $members = $this->normalizedGroupMembers($class);

            if (empty($members)) {
                return [];
            }

            return $query
                ->whereIn('student_group', $members)
                ->pluck('users.id')
                ->all();
        }

        return [];
    }

    protected function manualStudentIdsForClass(PraktikumClass $class, bool $activeOnly = true): array
    {
        return $class->students()
            ->when($activeOnly, function ($query) {
                $query->where('users.is_active', true);
            })
            ->where(function ($query) {
                $query->where('users.role', 'mahasiswa')
                    ->orWhereHas('roles', function ($roleQuery) {
                        $roleQuery->where('name', 'mahasiswa');
                    });
            })
            ->pluck('users.id')
            ->all();
    }

    protected function normalizedGroupMembers(PraktikumClass $class): array
    {
        return collect($class->group_members ?? [])
            ->map(fn ($group) => strtoupper((string) $group))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}