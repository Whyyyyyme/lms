<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Concerns\HandlesLmsNotifications;
use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\PraktikumClass;
use Carbon\Carbon;
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
            ->latest('opened_at')
            ->latest('session_date')
            ->paginate(10);

        $attendances->getCollection()->each(function (Attendance $attendance): void {
            $this->refreshAttendanceStatus($attendance);
        });

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
            'opened_at' => ['required', 'date'],
            'closed_at' => ['required', 'date', 'after:opened_at'],
        ], [
            'class_id.required' => 'Kelas praktikum wajib dipilih.',
            'class_id.exists' => 'Kelas praktikum tidak valid.',
            'opened_at.required' => 'Tanggal dan jam dibuka wajib diisi.',
            'opened_at.date' => 'Format tanggal dan jam dibuka tidak valid.',
            'closed_at.required' => 'Tanggal dan jam ditutup wajib diisi.',
            'closed_at.date' => 'Format tanggal dan jam ditutup tidak valid.',
            'closed_at.after' => 'Tanggal dan jam ditutup harus setelah tanggal dan jam dibuka.',
        ]);

        $class = $this->assistantClassOrFail((int) $validated['class_id']);

        $openedAt = Carbon::parse($validated['opened_at'], config('app.timezone'))->seconds(0);
        $closedAt = Carbon::parse($validated['closed_at'], config('app.timezone'))->seconds(0);

        if ($this->hasAttendanceOnSameDay($class, $openedAt)) {
            return back()
                ->withInput()
                ->withErrors([
                    'opened_at' => 'Absensi untuk kelas/mata kuliah ini pada tanggal tersebut sudah ada. Satu kelas hanya boleh membuat satu absensi dalam satu hari.',
                ]);
        }

        $isOpen = $openedAt->lessThanOrEqualTo(now()) && $closedAt->greaterThan(now());

        $attendance = Attendance::create([
            'class_id' => $class->id,
            'session_date' => $openedAt->toDateString(),
            'opened_by' => auth()->id(),
            'opened_at' => $openedAt,
            'closed_at' => $closedAt,
            'is_open' => $isOpen,
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

        $this->refreshAttendanceStatus($attendance);
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

        $this->refreshAttendanceStatus($attendance);
        $attendance->refresh();

        if (! $attendance->opened_at || ! $attendance->closed_at) {
            return back()->with('error', 'Sesi absensi belum memiliki jadwal dibuka dan ditutup yang lengkap.');
        }

        if ($attendance->opened_at->greaterThan(now())) {
            return back()->with('error', 'Sesi absensi belum bisa dibuka karena jadwal dibuka belum tiba.');
        }

        if ($attendance->closed_at->lessThanOrEqualTo(now())) {
            return back()->with('error', 'Sesi absensi tidak bisa dibuka karena jadwal tutup sudah lewat.');
        }

        Attendance::query()
            ->where('class_id', $attendance->class_id)
            ->whereKeyNot($attendance->id)
            ->where('is_open', true)
            ->update([
                'is_open' => false,
            ]);

        $attendance->update([
            'is_open' => true,
        ]);

        $this->syncAttendanceRecords($attendance);
        $this->notifyAttendanceOpened($attendance->fresh());

        return back()->with('success', 'Sesi absensi berhasil dibuka.');
    }

    public function close(Attendance $attendance): RedirectResponse
    {
        $this->assistantClassOrFail((int) $attendance->class_id);

        $this->refreshAttendanceStatus($attendance);
        $attendance->refresh();

        if ($attendance->opened_at && $attendance->opened_at->greaterThan(now())) {
            return back()->with('error', 'Sesi absensi belum dimulai. Jika ingin membatalkan, hapus sesi absensi ini.');
        }

        if ($attendance->closed_at && $attendance->closed_at->lessThanOrEqualTo(now())) {
            $attendance->update([
                'is_open' => false,
            ]);

            return back()->with('info', 'Sesi absensi sudah berada dalam status tertutup.');
        }

        $attendance->update([
            'is_open' => false,
            'closed_at' => now(),
        ]);

        return back()->with('success', 'Sesi absensi berhasil ditutup lebih awal.');
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

    private function refreshAttendanceStatus(Attendance $attendance): bool
    {
        return $attendance->syncOpenStatus();
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

        $class = $attendance->kelas;
        $students = $this->studentsForClass($class);

        if ($students->isEmpty()) {
            return;
        }

        $classInfo = $this->classContext($class);

        $this->notifyUsers(
            $students,
            'attendance_opened',
            'Absensi Dibuka',
            "Absensi untuk {$classInfo['label']} sudah dibuka.",
            [
                'attendance_id' => $attendance->id,
                'class_id' => $class->id,
                'course_name' => $classInfo['course_name'],
                'course_code' => $classInfo['course_code'],
                'class_name' => $classInfo['class_name'],
                'context_label' => $classInfo['label'],
                'session_date' => $attendance->session_date?->format('d M Y'),
                'opened_at' => $this->formatDateTimeWib($attendance->opened_at),
                'closed_at' => $this->formatDateTimeWib($attendance->closed_at),
                'url' => route('student.attendances.index'),
            ]
        );
    }

    private function hasAttendanceOnSameDay(PraktikumClass $class, Carbon $openedAt): bool
    {
        return Attendance::query()
            ->where('class_id', $class->id)
            ->whereDate('opened_at', $openedAt->toDateString())
            ->exists();
    }

    private function formatDateTimeWib($date): ?string
    {
        if (! $date) {
            return null;
        }

        return $date
            ->timezone(config('app.timezone', 'Asia/Jakarta'))
            ->format('d M Y H:i') . ' WIB';
    }
}