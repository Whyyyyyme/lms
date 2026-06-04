<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Material;
use App\Models\Submission;
use Illuminate\View\View;

class MahasiswaDashboardController extends Controller
{
    use ResolvesClassAccess;

    public function index(): View
    {
        $classIds = $this->studentClassIds();

        $classes = $this->studentClasses()
            ->load(['course.studySemester', 'course.academicYear', 'assistant']);

        $statistics = [
            'total_kelas' => count($classIds),

            'total_materi' => Material::published()
                ->whereIn('class_id', $classIds)
                ->count(),

            'total_tugas' => Assignment::published()
                ->whereIn('class_id', $classIds)
                ->count(),

            'tugas_belum_dikumpulkan' => Assignment::published()
                ->whereIn('class_id', $classIds)
                ->whereDoesntHave('submissions', function ($query) {
                    $query->where('student_id', auth()->id());
                })
                ->count(),

            'total_nilai' => Submission::where('student_id', auth()->id())
                ->whereNotNull('graded_at')
                ->whereHas('assignment', function ($query) use ($classIds) {
                    $query->published()
                        ->whereIn('class_id', $classIds);
                })
                ->count(),

            'absensi_terbuka' => Attendance::whereIn('class_id', $classIds)
                ->whereNotNull('opened_at')
                ->whereNotNull('closed_at')
                ->where('opened_at', '<=', now())
                ->where('closed_at', '>', now())
                ->count(),
        ];

        $this->attachClassCardData($classes);

        $latestMaterials = Material::with('kelas.course')
            ->published()
            ->whereIn('class_id', $classIds)
            ->latest('published_at')
            ->limit(5)
            ->get();

        $upcomingAssignments = Assignment::with([
                'kelas.course',
                'submissions' => function ($query) {
                    $query->where('student_id', auth()->id());
                },
            ])
            ->published()
            ->whereIn('class_id', $classIds)
            ->where('deadline', '>=', now())
            ->orderBy('deadline')
            ->limit(5)
            ->get();

        $announcements = Announcement::with('kelas.course')
            ->whereIn('class_id', $classIds)
            ->latest()
            ->limit(5)
            ->get();

        return view('student.dashboard', compact(
            'statistics',
            'classes',
            'latestMaterials',
            'upcomingAssignments',
            'announcements'
        ));
    }

    private function attachClassCardData($classes): void
    {
        if ($classes->isEmpty()) {
            return;
        }

        $classIds = $classes->pluck('id')->map(fn ($id) => (int) $id)->all();
        $studentId = (int) auth()->id();

        $materialCounts = Material::query()
            ->published()
            ->whereIn('class_id', $classIds)
            ->selectRaw('class_id, COUNT(*) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $assignmentCounts = Assignment::query()
            ->published()
            ->whereIn('class_id', $classIds)
            ->selectRaw('class_id, COUNT(*) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $pendingCounts = Assignment::query()
            ->published()
            ->whereIn('class_id', $classIds)
            ->whereDoesntHave('submissions', fn ($query) => $query->where('student_id', $studentId))
            ->selectRaw('class_id, COUNT(*) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $classes->each(function ($class) use ($materialCounts, $assignmentCounts, $pendingCounts): void {
            $classId = (int) $class->id;

            $class->setAttribute('published_materials_count', (int) ($materialCounts[$classId] ?? 0));
            $class->setAttribute('published_assignments_count', (int) ($assignmentCounts[$classId] ?? 0));
            $class->setAttribute('pending_assignments_count', (int) ($pendingCounts[$classId] ?? 0));
        });
    }
}
