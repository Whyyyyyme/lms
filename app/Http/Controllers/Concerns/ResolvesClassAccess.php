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

    protected function studentClassIds(?User $user = null): array
    {
        $user ??= auth()->user();

        $manualClassIds = $user->kelasDiikuti()->pluck('classes.id')->all();

        $semesterClassIds = [];
        if ($user->study_semester_id) {
            $semesterClassIds = PraktikumClass::query()
                ->whereHas('course', fn ($query) => $query->where('study_semester_id', $user->study_semester_id))
                ->pluck('classes.id')
                ->all();
        }

        // Kompatibilitas data lama: kalau users.kelas_id masih terisi, tetap ikut dihitung.
        if ($user->kelas_id) {
            $manualClassIds[] = $user->kelas_id;
        }

        return array_values(array_unique(array_merge($manualClassIds, $semesterClassIds)));
    }

    protected function studentClasses(?User $user = null): Collection
    {
        $user ??= auth()->user();

        return PraktikumClass::query()
            ->with(['course.studySemester', 'assistant'])
            ->whereIn('id', $this->studentClassIds($user))
            ->orderBy('name')
            ->get();
    }
}
