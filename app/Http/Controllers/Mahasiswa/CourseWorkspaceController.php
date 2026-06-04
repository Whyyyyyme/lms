<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Material;
use App\Models\PraktikumClass;
use App\Models\Submission;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CourseWorkspaceController extends Controller
{
    use ResolvesClassAccess;

    public function index(Request $request): View
    {
        $classIds = $this->studentClassIdArray();

        $classes = $classIds === []
            ? collect()
            : PraktikumClass::query()
                ->with(['course.studySemester', 'course.academicYear', 'assistant'])
                ->withCount([
                    'materials as published_materials_count' => fn ($query) => $query->published(),
                    'assignments as published_assignments_count' => fn ($query) => $query->published(),
                    'attendances as attendances_count',
                    'announcements as announcements_count',
                ])
                ->whereIn('id', $classIds)
                ->orderBy('name')
                ->get();

        $this->attachCourseProgressData($classes);

        $statistics = [
            'total_classes' => $classes->count(),
            'total_materials' => $classes->sum('published_materials_count'),
            'total_assignments' => $classes->sum('published_assignments_count'),
            'pending_assignments' => $classes->sum('pending_assignments_count'),
            'open_attendances' => $classes->sum('open_attendances_count'),
        ];

        $upcomingAssignments = Assignment::query()
            ->with(['kelas.course', 'submissions' => fn ($query) => $query->where('student_id', auth()->id())])
            ->published()
            ->whereIn('class_id', $classIds)
            ->where('deadline', '>=', now())
            ->orderBy('deadline')
            ->limit(5)
            ->get();

        $openAttendances = Attendance::query()
            ->with(['kelas.course', 'records' => fn ($query) => $query->where('student_id', auth()->id())])
            ->whereIn('class_id', $classIds)
            ->whereNotNull('opened_at')
            ->whereNotNull('closed_at')
            ->where('opened_at', '<=', now())
            ->where('closed_at', '>', now())
            ->orderBy('closed_at')
            ->limit(5)
            ->get();

        return view('student.courses.index', compact(
            'classes',
            'statistics',
            'upcomingAssignments',
            'openAttendances'
        ));
    }

    public function show(PraktikumClass $praktikumClass): View
    {
        $this->ensureStudentCanAccessClass($praktikumClass);

        $praktikumClass->loadMissing(['course.studySemester', 'course.academicYear', 'assistant']);

        $materials = Material::query()
            ->with(['kelas.course', 'creator'])
            ->published()
            ->where('class_id', $praktikumClass->id)
            ->latest('published_at')
            ->paginate(8, ['*'], 'materi_page');

        $assignments = Assignment::query()
            ->with([
                'kelas.course',
                'creator',
                'submissions' => fn ($query) => $query->where('student_id', auth()->id()),
            ])
            ->published()
            ->where('class_id', $praktikumClass->id)
            ->orderByRaw('CASE WHEN deadline >= CURRENT_TIMESTAMP THEN 0 ELSE 1 END')
            ->orderBy('deadline')
            ->paginate(8, ['*'], 'tugas_page');

        $attendances = Attendance::query()
            ->with(['records' => fn ($query) => $query->where('student_id', auth()->id())])
            ->where('class_id', $praktikumClass->id)
            ->latest('opened_at')
            ->latest('session_date')
            ->paginate(8, ['*'], 'absensi_page');

        $attendances->getCollection()->each(function (Attendance $attendance): void {
            $attendance->syncOpenStatus();
        });

        $announcements = Announcement::query()
            ->with('creator')
            ->where('class_id', $praktikumClass->id)
            ->latest()
            ->limit(5)
            ->get();

        $summary = $this->buildClassSummary($praktikumClass, $materials, $assignments, $attendances);

        return view('student.courses.show', [
            'class' => $praktikumClass,
            'materials' => $materials,
            'assignments' => $assignments,
            'attendances' => $attendances,
            'announcements' => $announcements,
            'summary' => $summary,
        ]);
    }

    private function attachCourseProgressData(Collection|EloquentCollection $classes): void
    {
        if ($classes->isEmpty()) {
            return;
        }

        $classIds = $classes->pluck('id')->map(fn ($id) => (int) $id)->all();
        $studentId = (int) auth()->id();

        $pendingAssignments = Assignment::query()
            ->published()
            ->whereIn('class_id', $classIds)
            ->whereDoesntHave('submissions', fn ($query) => $query->where('student_id', $studentId))
            ->selectRaw('class_id, COUNT(*) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $openAttendances = Attendance::query()
            ->whereIn('class_id', $classIds)
            ->whereNotNull('opened_at')
            ->whereNotNull('closed_at')
            ->where('opened_at', '<=', now())
            ->where('closed_at', '>', now())
            ->selectRaw('class_id, COUNT(*) as total')
            ->groupBy('class_id')
            ->pluck('total', 'class_id');

        $latestMaterials = Material::query()
            ->published()
            ->whereIn('class_id', $classIds)
            ->selectRaw('class_id, MAX(published_at) as latest_at')
            ->groupBy('class_id')
            ->pluck('latest_at', 'class_id');

        $nextAssignments = Assignment::query()
            ->published()
            ->whereIn('class_id', $classIds)
            ->where('deadline', '>=', now())
            ->orderBy('deadline')
            ->get()
            ->groupBy('class_id')
            ->map(fn ($items) => $items->first());

        $averageScores = Submission::query()
            ->join('assignments', 'submissions.assignment_id', '=', 'assignments.id')
            ->where('submissions.student_id', $studentId)
            ->whereIn('assignments.class_id', $classIds)
            ->whereNotNull('submissions.score')
            ->selectRaw('assignments.class_id, AVG(submissions.score) as average_score')
            ->groupBy('assignments.class_id')
            ->pluck('average_score', 'class_id');

        $classes->each(function (PraktikumClass $class) use ($pendingAssignments, $openAttendances, $latestMaterials, $nextAssignments, $averageScores): void {
            $classId = (int) $class->id;

            $class->setAttribute('pending_assignments_count', (int) ($pendingAssignments[$classId] ?? 0));
            $class->setAttribute('open_attendances_count', (int) ($openAttendances[$classId] ?? 0));
            $class->setAttribute('latest_material_at', $latestMaterials[$classId] ?? null);
            $class->setAttribute('next_assignment', $nextAssignments->get($classId));
            $class->setAttribute('average_score', $averageScores[$classId] ?? null);
        });
    }

    private function buildClassSummary(
        PraktikumClass $class,
        LengthAwarePaginator $materials,
        LengthAwarePaginator $assignments,
        LengthAwarePaginator $attendances
    ): array {
        $studentId = (int) auth()->id();

        $totalAssignments = Assignment::query()
            ->published()
            ->where('class_id', $class->id)
            ->count();

        $submittedAssignments = Assignment::query()
            ->published()
            ->where('class_id', $class->id)
            ->whereHas('submissions', fn ($query) => $query->where('student_id', $studentId))
            ->count();

        $openAttendances = Attendance::query()
            ->where('class_id', $class->id)
            ->whereNotNull('opened_at')
            ->whereNotNull('closed_at')
            ->where('opened_at', '<=', now())
            ->where('closed_at', '>', now())
            ->count();

        $averageScore = Submission::query()
            ->join('assignments', 'submissions.assignment_id', '=', 'assignments.id')
            ->where('submissions.student_id', $studentId)
            ->where('assignments.class_id', $class->id)
            ->whereNotNull('submissions.score')
            ->avg('submissions.score');

        $progress = $totalAssignments > 0
            ? (int) round(($submittedAssignments / $totalAssignments) * 100)
            : 0;

        return [
            'total_materials' => $materials->total(),
            'total_assignments' => $totalAssignments,
            'submitted_assignments' => $submittedAssignments,
            'pending_assignments' => max($totalAssignments - $submittedAssignments, 0),
            'total_attendances' => $attendances->total(),
            'open_attendances' => $openAttendances,
            'average_score' => $averageScore,
            'progress' => $progress,
        ];
    }

    private function ensureStudentCanAccessClass(PraktikumClass $class): void
    {
        abort_unless(
            in_array((int) $class->id, $this->studentClassIdArray(), true),
            403
        );
    }

    private function studentClassIdArray(): array
    {
        return collect($this->studentClassIds())
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();
    }
}
