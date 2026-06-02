<?php

namespace App\Http\Controllers\Concerns;

use App\Models\PraktikumClass;
use App\Models\User;
use App\Services\StudentAccessService;
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
        return app(StudentAccessService::class)->classIdsForStudent($user ?? auth()->user());
    }

    protected function studentClasses(?User $user = null): Collection
    {
        return app(StudentAccessService::class)->classesForStudent($user ?? auth()->user());
    }

    protected function classStudents(PraktikumClass $class): Collection
    {
        return app(StudentAccessService::class)->studentsForClass($class);
    }
}
