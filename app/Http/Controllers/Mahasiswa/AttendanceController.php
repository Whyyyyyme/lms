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
            ->with([
                'kelas.course.studySemester',
                'records' => function ($query) {
                    $query->where('student_id', auth()->id());
                },
            ])
            ->whereIn('class_id', $this->studentClassIds())
            ->latest('opened_at')
            ->latest('session_date')
            ->paginate(10);

        $attendances->getCollection()->each(function (Attendance $attendance): void {
            $this->refreshAttendanceStatus($attendance);
        });

        return view('student.attendances.index', compact('attendances'));
    }

    public function checkIn(Attendance $attendance): RedirectResponse
    {
        abort_unless(
            in_array((int) $attendance->class_id, $this->studentClassIds(), true),
            403
        );

        $this->refreshAttendanceStatus($attendance);
        $attendance->refresh();

        if ($attendance->opened_at && $attendance->opened_at->greaterThan(now())) {
            return back()->with('error', 'Sesi absensi belum dibuka.');
        }

        if ($attendance->closed_at && $attendance->closed_at->lessThanOrEqualTo(now())) {
            return back()->with('error', 'Sesi absensi sudah ditutup.');
        }

        if (! $attendance->is_open) {
            return back()->with('error', 'Sesi absensi belum dibuka atau sudah ditutup.');
        }

        $record = AttendanceRecord::query()
            ->where('attendance_id', $attendance->id)
            ->where('student_id', auth()->id())
            ->first();

        if ($record?->status === 'hadir') {
            return back()->with('status', 'Kamu sudah melakukan check-in absensi.');
        }

        if ($record?->status === 'izin') {
            return back()->with('error', 'Status kamu sudah ditandai izin oleh asisten. Hubungi asisten jika perlu koreksi.');
        }

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

    private function refreshAttendanceStatus(Attendance $attendance): void
    {
        $now = now();

        if ($attendance->closed_at && $attendance->closed_at->lessThanOrEqualTo($now)) {
            if ($attendance->is_open) {
                $attendance->update([
                    'is_open' => false,
                ]);
            }

            return;
        }

        if (
            $attendance->opened_at
            && $attendance->opened_at->lessThanOrEqualTo($now)
            && (! $attendance->closed_at || $attendance->closed_at->greaterThan($now))
        ) {
            if (! $attendance->is_open) {
                $attendance->update([
                    'is_open' => true,
                ]);
            }
        }
    }
}
