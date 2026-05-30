<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Material;
use App\Models\PraktikumClass;
use App\Models\StudySemester;
use App\Models\Submission;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $statistics = [
            'total_users' => User::whereIn('role', ['asisten', 'mahasiswa'])->count(),
            'total_asisten' => User::role('asisten')->count(),
            'total_mahasiswa' => User::role('mahasiswa')->count(),
            'total_mahasiswa_tanpa_semester' => User::role('mahasiswa')->whereNull('study_semester_id')->count(),

            'total_semesters' => StudySemester::count(),
            'total_active_semesters' => StudySemester::active()->count(),

            'total_academic_years' => AcademicYear::count(),
            'total_courses' => Course::count(),
            'total_active_courses' => Course::active()->count(),

            'total_classes' => PraktikumClass::count(),
            'total_active_classes' => PraktikumClass::active()->count(),
            'total_classes_without_assistant' => PraktikumClass::whereNull('assistant_id')->count(),

            'total_materials' => Material::count(),
            'total_assignments' => Assignment::count(),
            'total_submissions' => Submission::count(),
            'total_ungraded_submissions' => Submission::whereNull('graded_at')->count(),

            'total_attendances' => Attendance::count(),
            'total_open_attendances' => Attendance::open()->count(),
        ];

        $semesterSummaries = StudySemester::query()
            ->withCount([
                'courses',
                'students as students_count' => function ($query) {
                    $query->where('role', 'mahasiswa');
                },
            ])
            ->orderBy('level')
            ->get();

        $latestUsers = User::query()
            ->with(['roles', 'studySemester'])
            ->where(function ($query) {
                $query->whereIn('role', ['asisten', 'mahasiswa'])
                    ->orWhereHas('roles', function ($roleQuery) {
                        $roleQuery->whereIn('name', ['asisten', 'mahasiswa']);
                    });
            })
            ->latest()
            ->limit(5)
            ->get();

        $latestClasses = PraktikumClass::query()
            ->with(['course.studySemester', 'assistant'])
            ->latest()
            ->limit(5)
            ->get();

        $latestSubmissions = Submission::query()
            ->with(['student', 'assignment.kelas.course'])
            ->latest('submitted_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'statistics',
            'semesterSummaries',
            'latestUsers',
            'latestClasses',
            'latestSubmissions'
        ));
    }
}