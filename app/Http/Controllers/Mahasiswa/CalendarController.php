<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Attendance;
use Illuminate\View\View;

class CalendarController extends Controller
{
    use ResolvesClassAccess;

    public function schedule(): View
    {
        $classes = $this->studentClasses();

        return view('student.schedule.index', compact('classes'));
    }

    public function index(): View
    {
        $classIds = $this->studentClassIds();

        $assignments = Assignment::with('kelas.course')
            ->whereIn('class_id', $classIds)
            ->orderBy('deadline')
            ->get();

        $attendances = Attendance::with('kelas.course')
            ->whereIn('class_id', $classIds)
            ->orderBy('session_date')
            ->get();

        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        return view('student.calendar.index', compact('assignments', 'attendances', 'activeAcademicYear'));
    }
}
