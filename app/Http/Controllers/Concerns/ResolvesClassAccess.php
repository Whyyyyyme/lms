<?php

namespace App\Http\Controllers\Concerns;

use App\Models\PraktikumClass;
use App\Models\User;
use App\Services\StudentAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

trait ResolvesClassAccess
{
    protected function studentAccessService(): StudentAccessService
    {
        return app(StudentAccessService::class);
    }

    /**
     * Query kelas yang sedang aktif untuk asisten.
     *
     * Kelas dari tahun akademik nonaktif tidak lagi muncul di workspace asisten.
     * Mahasiswa tetap bisa melihatnya melalui tab riwayat masing-masing.
     */
    protected function assistantClassesQuery(?User $user = null): Builder
    {
        $user ??= auth()->user();

        return PraktikumClass::query()
            ->active()
            ->where('assistant_id', $user->id)
            ->whereHas('course', function ($query) {
                $query->where('is_active', true)
                    ->where(function ($query) {
                        $query->whereHas('academicYear', function ($academicYearQuery) {
                            $academicYearQuery->where('is_active', true);
                        })->orWhereDoesntHave('academicYear');
                    });
            });
    }

    protected function assistantClassOrFail(int $classId, ?User $user = null): PraktikumClass
    {
        return $this->assistantClassesQuery($user)->findOrFail($classId);
    }

    /**
     * Mengambil ID kelas aktif yang boleh diakses mahasiswa.
     */
    protected function studentClassIds(?User $user = null): array
    {
        $user ??= auth()->user();

        return $this->studentAccessService()->activeClassIdsForStudent($user);
    }

    /**
     * Mengambil ID kelas riwayat dari tahun akademik yang sudah nonaktif.
     */
    protected function studentArchivedClassIds(?User $user = null): array
    {
        $user ??= auth()->user();

        return $this->studentAccessService()->archivedClassIdsForStudent($user);
    }

    /**
     * Mengambil semua ID kelas yang boleh dibaca mahasiswa, baik aktif maupun riwayat.
     */
    protected function studentAllClassIds(?User $user = null): array
    {
        $user ??= auth()->user();

        return $this->studentAccessService()->allClassIdsForStudent($user);
    }

    protected function studentClasses(?User $user = null): Collection
    {
        $user ??= auth()->user();

        return $this->studentAccessService()->activeClassesForStudent($user);
    }

    protected function studentArchivedClasses(?User $user = null): Collection
    {
        $user ??= auth()->user();

        return $this->studentAccessService()->archivedClassesForStudent($user);
    }

    protected function studentAllClasses(?User $user = null): Collection
    {
        $user ??= auth()->user();

        return $this->studentAccessService()->allClassesForStudent($user);
    }

    /**
     * Query dasar mahasiswa.
     *
     * Tetap disediakan untuk controller lama yang masih butuh query manual.
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
     */
    protected function studentIdsForClass(PraktikumClass $class, bool $activeOnly = true): array
    {
        return $this->studentsForClass($class, $activeOnly)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    protected function studentsForClass(PraktikumClass $class, bool $activeOnly = true): Collection
    {
        $students = $this->studentAccessService()->studentsForClass($class);

        if ($activeOnly) {
            $students = $students->filter(fn ($student) => (bool) $student->is_active);
        }

        return $students
            ->filter(fn ($student) => $student instanceof User)
            ->unique('id')
            ->sortBy([
                fn ($student) => $student->student_group ?? '',
                fn ($student) => $student->name ?? '',
            ])
            ->values();
    }

    /**
     * Alias untuk controller lama yang masih memanggil classStudents().
     */
    protected function classStudents(PraktikumClass $class, bool $activeOnly = true): Collection
    {
        return $this->studentsForClass($class, $activeOnly);
    }

    /**
     * Alias untuk controller lama yang butuh ID mahasiswa kelas.
     */
    protected function classStudentIds(PraktikumClass $class, bool $activeOnly = true): array
    {
        return $this->studentIdsForClass($class, $activeOnly);
    }

    protected function studentCountForClass(PraktikumClass $class, bool $activeOnly = true): int
    {
        return $this->studentsForClass($class, $activeOnly)->count();
    }

    /**
     * Alias untuk controller lama yang masih butuh count mahasiswa kelas.
     */
    protected function classStudentCount(PraktikumClass $class, bool $activeOnly = true): int
    {
        return $this->studentCountForClass($class, $activeOnly);
    }

    /**
     * Kompatibilitas untuk controller lama.
     */
    protected function automaticStudentIdsForClass(PraktikumClass $class, bool $activeOnly = true): array
    {
        return $this->studentIdsForClass($class, $activeOnly);
    }

    /**
     * Kompatibilitas untuk controller lama yang masih memanggil manualStudentIdsForClass().
     */
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
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Kompatibilitas untuk controller/view lama yang masih membaca group_members.
     */
    protected function normalizedGroupMembers(PraktikumClass $class): array
    {
        $groupMembers = $class->group_members ?? [];

        if ($groupMembers instanceof Collection) {
            $groupMembers = $groupMembers->all();
        }

        if (is_string($groupMembers)) {
            $decoded = json_decode($groupMembers, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $groupMembers = $decoded;
            } else {
                $groupMembers = preg_split('/[,;\/|&\s]+/', $groupMembers) ?: [];
            }
        }

        if (! is_array($groupMembers)) {
            $groupMembers = [$groupMembers];
        }

        return collect($groupMembers)
            ->map(function ($group) {
                if (is_array($group)) {
                    $group = $group['student_group']
                        ?? $group['class_group']
                        ?? $group['group']
                        ?? $group['rombel']
                        ?? $group['kelas']
                        ?? $group['name']
                        ?? $group['value']
                        ?? null;
                }

                if (is_object($group)) {
                    $array = (array) $group;

                    $group = $array['student_group']
                        ?? $array['class_group']
                        ?? $array['group']
                        ?? $array['rombel']
                        ?? $array['kelas']
                        ?? $array['name']
                        ?? $array['value']
                        ?? null;
                }

                if ($group === null) {
                    return null;
                }

                $group = strtoupper(trim((string) $group));
                $group = preg_replace('/\b(KELAS|ROMBEL|GROUP|GRUP|GABUNGAN|COMBINED)\b/u', '', $group);
                $group = trim((string) $group);
                $group = trim($group, " \t\n\r\0\x0B:-_");

                return $group !== '' ? $group : null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
