<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Material;
use App\Models\Submission;
use Illuminate\View\View;

class AssistantDashboardController extends Controller
{
    use ResolvesClassAccess;

    public function index(): View
    {
        $classes = $this->assistantClassesQuery()
            ->with(['course.studySemester', 'students.studySemester'])
            ->withCount(['materials', 'assignments', 'attendances'])
            ->get();

        $classes->each(function ($class): void {
            $class->setAttribute('resolved_students_count', $this->classStudents($class)->count());
        });

        $classIds = $classes->pluck('id');

        $statistics = [
            'total_kelas' => $classes->count(),
            'total_mahasiswa' => $classes->sum('resolved_students_count'),
            'total_materi' => Material::whereIn('class_id', $classIds)->count(),
            'total_tugas' => Assignment::whereIn('class_id', $classIds)->count(),
            'total_submission_belum_dinilai' => Submission::whereHas('assignment', fn ($query) => $query->whereIn('class_id', $classIds))->whereNull('graded_at')->count(),
            'total_absensi_terbuka' => Attendance::whereIn('class_id', $classIds)->where('is_open', true)->count(),
        ];

        $latestSubmissions = Submission::with(['student', 'assignment.kelas.course'])
            ->whereHas('assignment', fn ($query) => $query->whereIn('class_id', $classIds))
            ->latest('submitted_at')
            ->limit(5)
            ->get();

        return view('assistant.dashboard', compact('classes', 'statistics', 'latestSubmissions'));
    }
}
