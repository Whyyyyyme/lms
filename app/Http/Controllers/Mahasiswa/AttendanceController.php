<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    use ResolvesClassAccess;

    public function index(): View
    {
        $attendances = Attendance::query()
            ->with(['kelas.course', 'records' => fn ($query) => $query->where('student_id', auth()->id())])
            ->whereIn('class_id', $this->studentClassIds())
            ->latest('session_date')
            ->paginate(10);

        return view('student.attendances.index', compact('attendances'));
    }

    public function checkIn(Attendance $attendance): RedirectResponse
    {
        abort_unless(in_array($attendance->class_id, $this->studentClassIds(), true), 403);
        abort_unless($attendance->is_open, 422, 'Sesi absensi belum dibuka atau sudah ditutup.');

        AttendanceRecord::updateOrCreate(
            [
                'attendance_id' => $attendance->id,
                'student_id' => auth()->id(),
            ],
            [
                'status' => 'hadir',
                'checked_at' => now(),
            ]
        );

        return back()->with('success', 'Absensi berhasil. Status kamu tercatat hadir.');
    }
}
