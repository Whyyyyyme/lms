<?php

namespace App\Services;

use App\Models\PraktikumClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class StudentAccessService
{
    /**
     * Ambil ID kelas yang boleh diakses mahasiswa.
     * Sumber utama: semester mahasiswa -> mata kuliah semester -> kelas praktikum.
     * Sumber tambahan: class_students dan users.kelas_id untuk kompatibilitas data lama/manual.
     *
     * @return array<int>
     */
    public function classIdsForStudent(?User $student): array
    {
        if (! $student) {
            return [];
        }

        $classIds = collect();

        if ($student->study_semester_id) {
            $classIds = $classIds->merge(
                PraktikumClass::query()
                    ->active()
                    ->whereHas('course', function ($query) use ($student) {
                        $query->where('study_semester_id', $student->study_semester_id)
                            ->where('is_active', true);
                    })
                    ->pluck('classes.id')
            );
        }

        if (method_exists($student, 'kelasDiikuti')) {
            $classIds = $classIds->merge($student->kelasDiikuti()->pluck('classes.id'));
        }

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
     * Dipakai untuk absensi, notifikasi, dan rekap agar tidak bergantung ke class_students saja.
     */
    public function studentsForClass(?PraktikumClass $class): Collection
    {
        if (! $class) {
            return collect();
        }

        $class->loadMissing(['course.studySemester', 'students.studySemester']);

        $students = collect();

        if ($class->course?->study_semester_id) {
            $students = $students->merge(
                User::query()
                    ->role('mahasiswa')
                    ->active()
                    ->with('studySemester')
                    ->where('study_semester_id', $class->course->study_semester_id)
                    ->orderBy('name')
                    ->get()
            );
        }

        if ($class->relationLoaded('students')) {
            $students = $students->merge($class->students);
        } else {
            $students = $students->merge($class->students()->with('studySemester')->active()->get());
        }

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
}
