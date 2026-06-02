<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\LmsNotification;
use App\Models\PraktikumClass;
use App\Models\User;
use App\Services\StudentAccessService;
use Illuminate\Support\Str;
use Livewire\Component;

class AbsensiLive extends Component
{
    public ?int $classId = null;

    public ?int $selectedAttendanceId = null;

    public ?string $flashMessage = null;

    public function mount(?int $classId = null): void
    {
        $this->classId = $classId;
    }

    public function openSession(int $classId): void
    {
        $class = PraktikumClass::query()
            ->with(['course.studySemester', 'students.studySemester'])
            ->whereKey($classId)
            ->firstOrFail();

        abort_unless($this->canManageClass($class), 403);

        $hasOpenSession = Attendance::query()
            ->where('class_id', $class->id)
            ->where('is_open', true)
            ->exists();

        if ($hasOpenSession) {
            $this->flashMessage = 'Masih ada sesi absensi yang terbuka untuk kelas ini.';
            return;
        }

        $attendance = Attendance::create([
            'class_id' => $class->id,
            'session_date' => now()->toDateString(),
            'opened_by' => auth()->id(),
            'opened_at' => now(),
            'is_open' => true,
        ]);

        foreach (app(StudentAccessService::class)->studentsForClass($class) as $student) {
            AttendanceRecord::firstOrCreate(
                [
                    'attendance_id' => $attendance->id,
                    'student_id' => $student->id,
                ],
                [
                    'status' => 'alpha',
                    'checked_at' => null,
                ]
            );

            $this->createNotification(
                $student,
                'Sesi absensi dibuka',
                'Absensi untuk kelas ' . $class->name . ' sudah dibuka. Silakan check-in sekarang.',
                [
                    'attendance_id' => $attendance->id,
                    'class_id' => $class->id,
                    'url' => route('student.attendances.index'),
                ]
            );
        }

        $this->selectedAttendanceId = $attendance->id;
        $this->flashMessage = 'Sesi absensi berhasil dibuka.';
        $this->dispatch('attendance-opened');
    }

    public function closeSession(int $attendanceId): void
    {
        $attendance = Attendance::query()->with('kelas')->findOrFail($attendanceId);

        abort_unless($this->canManageClass($attendance->kelas), 403);

        $attendance->update([
            'is_open' => false,
            'closed_at' => now(),
        ]);

        $this->flashMessage = 'Sesi absensi berhasil ditutup.';
        $this->dispatch('attendance-closed');
    }

    public function checkIn(int $attendanceId): void
    {
        $attendance = Attendance::query()->findOrFail($attendanceId);

        abort_unless($attendance->is_open, 422, 'Sesi absensi belum dibuka atau sudah ditutup.');
        abort_unless(in_array((int) $attendance->class_id, $this->studentClassIds(), true), 403);

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

        $this->flashMessage = 'Absensi berhasil. Status kamu tercatat hadir.';
        $this->dispatch('attendance-checked-in');
    }

    public function updateStudentStatus(int $attendanceId, int $studentId, string $status): void
    {
        abort_unless(in_array($status, ['hadir', 'izin', 'alpha'], true), 422);

        $attendance = Attendance::query()->with('kelas')->findOrFail($attendanceId);

        abort_unless($this->canManageClass($attendance->kelas), 403);

        AttendanceRecord::updateOrCreate(
            [
                'attendance_id' => $attendance->id,
                'student_id' => $studentId,
            ],
            [
                'status' => $status,
                'checked_at' => $status === 'hadir' ? now() : null,
            ]
        );

        $this->flashMessage = 'Status absensi mahasiswa berhasil diperbarui.';
        $this->dispatch('attendance-status-updated');
    }

    public function selectAttendance(int $attendanceId): void
    {
        $attendance = Attendance::query()->with('kelas')->findOrFail($attendanceId);

        if (auth()->user()->hasRole('asisten')) {
            abort_unless($this->canManageClass($attendance->kelas), 403);
        } else {
            abort_unless(in_array((int) $attendance->class_id, $this->studentClassIds(), true), 403);
        }

        $this->selectedAttendanceId = $attendance->id;
    }

    private function assistantClassIds(): array
    {
        return PraktikumClass::query()
            ->where('assistant_id', auth()->id())
            ->when($this->classId, fn ($query) => $query->whereKey($this->classId))
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function studentClassIds(): array
    {
        $ids = collect(app(StudentAccessService::class)->classIdsForStudent(auth()->user()));

        if ($this->classId) {
            $ids = $ids->filter(fn ($id) => (int) $id === (int) $this->classId);
        }

        return $ids->unique()->values()->all();
    }

    private function canManageClass(?PraktikumClass $class): bool
    {
        return $class !== null
            && auth()->user()->hasRole('asisten')
            && (int) $class->assistant_id === (int) auth()->id();
    }

    private function createNotification(User $user, string $title, string $message, array $data = []): void
    {
        LmsNotification::create([
            'id' => (string) Str::uuid(),
            'type' => 'attendance.opened',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'user_id' => $user->id,
            'title' => $title,
            'message' => $message,
            'data' => $data,
        ]);
    }

    public function render()
    {
        $user = auth()->user();

        if ($user->hasRole('asisten')) {
            $classes = PraktikumClass::query()
                ->with(['course', 'activeAttendance'])
                ->whereIn('id', $this->assistantClassIds())
                ->orderBy('name')
                ->get();

            $attendances = Attendance::query()
                ->with(['kelas.course', 'records.student'])
                ->withCount([
                    'records as hadir_count' => fn ($query) => $query->where('status', 'hadir'),
                    'records as izin_count' => fn ($query) => $query->where('status', 'izin'),
                    'records as alpha_count' => fn ($query) => $query->where('status', 'alpha'),
                ])
                ->whereIn('class_id', $classes->pluck('id'))
                ->latest('session_date')
                ->latest('opened_at')
                ->limit(10)
                ->get();

            $selectedAttendance = $this->selectedAttendanceId
                ? Attendance::query()->with(['kelas.course', 'records.student'])->find($this->selectedAttendanceId)
                : $attendances->first();

            return view('livewire.absensi-live', [
                'mode' => 'asisten',
                'classes' => $classes,
                'attendances' => $attendances,
                'selectedAttendance' => $selectedAttendance,
                'activeAttendances' => collect(),
            ]);
        }

        $activeAttendances = Attendance::query()
            ->with(['kelas.course', 'records' => fn ($query) => $query->where('student_id', auth()->id())])
            ->whereIn('class_id', $this->studentClassIds())
            ->where('is_open', true)
            ->latest('opened_at')
            ->get();

        $attendances = Attendance::query()
            ->with(['kelas.course', 'records' => fn ($query) => $query->where('student_id', auth()->id())])
            ->whereIn('class_id', $this->studentClassIds())
            ->latest('session_date')
            ->latest('opened_at')
            ->limit(10)
            ->get();

        return view('livewire.absensi-live', [
            'mode' => 'mahasiswa',
            'classes' => collect(),
            'attendances' => $attendances,
            'selectedAttendance' => null,
            'activeAttendances' => $activeAttendances,
        ]);
    }
}
