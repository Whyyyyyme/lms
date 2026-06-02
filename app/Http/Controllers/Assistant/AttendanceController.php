<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Concerns\HandlesLmsNotifications;
use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\PraktikumClass;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    use HandlesLmsNotifications, ResolvesClassAccess;

    public function index(): View
    {
        $classIds = $this->assistantClassesQuery()->pluck('id');

        $attendances = Attendance::query()
            ->with(['kelas.course.studySemester', 'opener'])
            ->withCount('records')
            ->whereIn('class_id', $classIds)
            ->latest('session_date')
            ->paginate(10);

        return view('assistant.attendances.index', compact('attendances'));
    }

    public function create(): View
    {
        return view('assistant.attendances.create', [
            'classes' => $this->assistantClassesQuery()
                ->with(['course.studySemester'])
                ->where('is_active', true)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'session_date' => ['required', 'date'],
            'open_now' => ['nullable', 'boolean'],
        ], [
            'class_id.required' => 'Kelas praktikum wajib dipilih.',
            'class_id.exists' => 'Kelas praktikum tidak valid.',
            'session_date.required' => 'Tanggal sesi absensi wajib diisi.',
            'session_date.date' => 'Format tanggal sesi absensi tidak valid.',
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

        $this->syncAttendanceRecords($attendance);

        if ($attendance->is_open) {
            $this->notifyAttendanceOpened($attendance);
        }

        return redirect()
            ->route('assistant.attendances.show', $attendance)
            ->with('success', 'Sesi absensi berhasil dibuat.');
    }

    public function show(Attendance $attendance): View
    {
        $this->assistantClassOrFail((int) $attendance->class_id);

        $this->syncAttendanceRecords($attendance);

        $attendance->load([
            'kelas.course.studySemester',
            'opener',
            'records.student.studySemester',
        ]);

        $attendance->records = $attendance->records
            ->sortBy([
                fn ($record) => $record->student?->student_group ?? '',
                fn ($record) => $record->student?->name ?? '',
            ])
            ->values();

        return view('assistant.attendances.show', compact('attendance'));
    }

    public function open(Attendance $attendance): RedirectResponse
    {
        $this->assistantClassOrFail((int) $attendance->class_id);

        Attendance::where('class_id', $attendance->class_id)
            ->whereKeyNot($attendance->id)
            ->update([
                'is_open' => false,
                'closed_at' => now(),
            ]);

        $attendance->update([
            'is_open' => true,
            'opened_at' => $attendance->opened_at ?? now(),
            'closed_at' => null,
        ]);

        $this->syncAttendanceRecords($attendance);
        $this->notifyAttendanceOpened($attendance);

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
        ], [
            'status.required' => 'Status absensi wajib dipilih.',
            'status.in' => 'Status absensi tidak valid.',
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

        $hasImportantRecords = $attendance->records()
            ->whereIn('status', ['hadir', 'izin'])
            ->exists();

        if ($hasImportantRecords) {
            return back()->with('error', 'Sesi absensi tidak bisa dihapus karena sudah memiliki record hadir/izin.');
        }

        $attendance->delete();

        return redirect()
            ->route('assistant.attendances.index')
            ->with('success', 'Sesi absensi berhasil dihapus.');
    }

    private function syncAttendanceRecords(Attendance $attendance): void
    {
        $attendance->loadMissing('kelas.course.studySemester');

        if (! $attendance->kelas instanceof PraktikumClass) {
            return;
        }

        $students = $this->studentsForClass($attendance->kelas);

        foreach ($students as $student) {
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
        $attendance->loadMissing('kelas.course.studySemester');

        if (! $attendance->kelas instanceof PraktikumClass) {
            return;
        }

        $students = $this->studentsForClass($attendance->kelas);

        if ($students->isEmpty()) {
            return;
        }

        $this->notifyUsers(
            $students,
            'attendance_opened',
            'Absensi Praktikum Dibuka',
            "Absensi untuk {$attendance->kelas->name} sudah dibuka. Silakan check-in sekarang.",
            [
                'attendance_id' => $attendance->id,
                'class_id' => $attendance->class_id,
            ]
        );
    }
}