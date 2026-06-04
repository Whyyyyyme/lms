<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Material;
use App\Models\PraktikumClass;
use App\Models\Submission;
use Illuminate\View\View;

class CourseWorkspaceController extends Controller
{
    use ResolvesClassAccess;

    public function index(): View
    {
        $classes = $this->assistantClassesQuery()
            ->with(['course.studySemester', 'assistant'])
            ->withCount(['materials', 'assignments', 'attendances'])
            ->orderBy('name')
            ->get();

        $classes->each(function (PraktikumClass $class): void {
            $class->setAttribute('resolved_students_count', $this->classStudents($class)->count());
        });

        $classIds = $classes->pluck('id');

        $statistics = [
            'total_kelas' => $classes->count(),
            'total_mahasiswa' => $classes->sum('resolved_students_count'),
            'total_materi' => Material::whereIn('class_id', $classIds)->count(),
            'total_tugas' => Assignment::whereIn('class_id', $classIds)->count(),
            'total_absensi_terbuka' => Attendance::whereIn('class_id', $classIds)->where('is_open', true)->count(),
            'total_submission_belum_dinilai' => Submission::whereHas('assignment', function ($query) use ($classIds) {
                $query->whereIn('class_id', $classIds);
            })->whereNull('graded_at')->count(),
        ];

        return view('assistant.courses.index', compact('classes', 'statistics'));
    }

    public function show(PraktikumClass $praktikumClass): View
    {
        $class = $this->assistantClassOrFail((int) $praktikumClass->id);

        $class->loadMissing(['course.studySemester', 'assistant']);

        $students = $this->classStudents($class);

        $materials = Material::query()
            ->with(['creator'])
            ->where('class_id', $class->id)
            ->latest('published_at')
            ->latest()
            ->get();

        $assignments = Assignment::query()
            ->with(['creator'])
            ->withCount('submissions')
            ->where('class_id', $class->id)
            ->latest('deadline')
            ->get();

        $attendances = Attendance::query()
            ->with(['opener'])
            ->withCount('records')
            ->where('class_id', $class->id)
            ->latest('opened_at')
            ->latest('session_date')
            ->get();

        $attendances->each(function (Attendance $attendance): void {
            $attendance->syncOpenStatus();
        });

        $latestSubmissions = Submission::query()
            ->with(['student', 'assignment'])
            ->whereHas('assignment', function ($query) use ($class) {
                $query->where('class_id', $class->id);
            })
            ->latest('submitted_at')
            ->limit(8)
            ->get();

        $statistics = [
            'total_mahasiswa' => $students->count(),
            'total_materi' => $materials->count(),
            'total_tugas' => $assignments->count(),
            'total_absensi' => $attendances->count(),
            'total_submission' => $latestSubmissions->count(),
            'total_belum_dinilai' => Submission::whereHas('assignment', function ($query) use ($class) {
                $query->where('class_id', $class->id);
            })->whereNull('graded_at')->count(),
        ];

        return view('assistant.courses.show', [
            'class' => $class,
            'students' => $students,
            'materials' => $materials,
            'assignments' => $assignments,
            'attendances' => $attendances,
            'latestSubmissions' => $latestSubmissions,
            'statistics' => $statistics,
        ]);
    }
}
