<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Concerns\HandlesLmsNotifications;
use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    use HandlesLmsNotifications, ResolvesClassAccess;

    public function index(): View
    {
        $attendances = Attendance::query()
            ->with(['kelas.course', 'opener'])
            ->withCount('records')
            ->whereIn('class_id', $this->assistantClassesQuery()->pluck('id'))
            ->latest('session_date')
            ->paginate(10);

        return view('assistant.attendances.index', compact('attendances'));
    }

    public function create(): View
    {
        return view('assistant.attendances.create', [
            'classes' => $this->assistantClassesQuery()->with('course')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'session_date' => ['required', 'date'],
            'open_now' => ['nullable', 'boolean'],
        ]);

        $class = $this->assistantClassOrFail((int) $validated['class_id']);

        $attendance = Attendance::create([
            'class_id' => $class->id,
            'session_date' => $validated['session_date'],
            'opened_by' => auth()->id(),
            'opened_at' => $request->boolean('open_now') ? now() : null,
            'closed_at' => null,
            'is_open' => $request->boolean('open_now'),
        ]);

        $this->syncAttendanceRecordsForClass($attendance, $class);

        if ($attendance->is_open) {
            $this->notifyAttendanceOpened($attendance);
        }

        return redirect()->route('assistant.attendances.show', $attendance)->with('success', 'Sesi absensi berhasil dibuat.');
    }

    public function show(Attendance $attendance): View
    {
        $this->assistantClassOrFail((int) $attendance->class_id);
        $attendance->load(['kelas.course', 'opener', 'records.student']);

        return view('assistant.attendances.show', compact('attendance'));
    }

    public function open(Attendance $attendance): RedirectResponse
    {
        $this->assistantClassOrFail((int) $attendance->class_id);

        Attendance::where('class_id', $attendance->class_id)
            ->whereKeyNot($attendance->id)
            ->update(['is_open' => false, 'closed_at' => now()]);

        $attendance->update([
            'is_open' => true,
            'opened_at' => $attendance->opened_at ?? now(),
            'closed_at' => null,
        ]);

        $this->syncAttendanceRecordsForClass($attendance->fresh(), $attendance->kelas);
        $this->notifyAttendanceOpened($attendance->fresh(['kelas.course.studySemester', 'kelas.students.studySemester']));

        return back()->with('success', 'Sesi absensi berhasil dibuka.');
    }

    public function close(Attendance $attendance): RedirectResponse
    {
        $this->assistantClassOrFail((int) $attendance->class_id);

        $attendance->update([
            'is_open' => false,
            'closed_at' => now(),
        ]);

        return back()->with('success', 'Sesi absensi berhasil ditutup.');
    }

    public function updateRecord(Request $request, Attendance $attendance, AttendanceRecord $record): RedirectResponse
    {
        $this->assistantClassOrFail((int) $attendance->class_id);
        abort_unless((int) $record->attendance_id === (int) $attendance->id, 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in(['hadir', 'izin', 'alpha'])],
        ]);

        $record->update([
            'status' => $validated['status'],
            'checked_at' => $validated['status'] === 'alpha' ? null : now(),
        ]);

        return back()->with('success', 'Status absensi berhasil diperbarui.');
    }

    public function destroy(Attendance $attendance): RedirectResponse
    {
        $this->assistantClassOrFail((int) $attendance->class_id);
        $attendance->delete();

        return redirect()->route('assistant.attendances.index')->with('success', 'Sesi absensi berhasil dihapus.');
    }


    private function syncAttendanceRecordsForClass(Attendance $attendance, $class): void
    {
        foreach ($this->classStudents($class) as $student) {
            AttendanceRecord::firstOrCreate([
                'attendance_id' => $attendance->id,
                'student_id' => $student->id,
            ], [
                'status' => 'alpha',
                'checked_at' => null,
            ]);
        }
    }

    private function notifyAttendanceOpened(Attendance $attendance): void
    {
        $attendance->loadMissing('kelas.course.studySemester', 'kelas.students.studySemester');

        $this->notifyUsers(
            $this->classStudents($attendance->kelas),
            'attendance_opened',
            'Absensi Praktikum Dibuka',
            "Absensi untuk {$attendance->kelas->name} sudah dibuka. Silakan check-in sekarang.",
            ['attendance_id' => $attendance->id, 'class_id' => $attendance->class_id]
        );
    }
}
