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

        $ids = $user->kelasDiikuti()->pluck('classes.id')->all();

        if ($user->kelas_id && ! in_array($user->kelas_id, $ids, true)) {
            $ids[] = $user->kelas_id;
        }

        return array_values(array_unique($ids));
    }

    protected function studentClasses(?User $user = null): Collection
    {
        $user ??= auth()->user();

        return PraktikumClass::query()
            ->with(['course', 'assistant'])
            ->whereIn('id', $this->studentClassIds($user))
            ->orderBy('name')
            ->get();
    }
}
