<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\PraktikumClass;
use App\Services\StudentAccessService;
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

    /**
     * Alur lama: asisten klik buka lalu sistem langsung membuat absensi.
     * Alur baru: asisten wajib menentukan opened_at dan closed_at dari halaman create.
     */
    public function openSession(int $classId): void
    {
        $class = PraktikumClass::query()
            ->whereKey($classId)
            ->firstOrFail();

        abort_unless($this->canManageClass($class), 403);

        $this->flashMessage = 'Untuk membuat absensi, gunakan tombol Buat Sesi Absensi agar tanggal/jam dibuka dan ditutup bisa ditentukan.';

        $this->redirectRoute('assistant.attendances.create');
    }

    public function closeSession(int $attendanceId): void
    {
        $attendance = Attendance::query()
            ->with('kelas')
            ->findOrFail($attendanceId);

        abort_unless($this->canManageClass($attendance->kelas), 403);

        $this->refreshAttendanceStatus($attendance);
        $attendance->refresh();

        if ($attendance->opened_at && $attendance->opened_at->greaterThan(now())) {
            $this->flashMessage = 'Sesi absensi belum dimulai. Jika ingin membatalkan, hapus sesi absensi dari halaman kelola absensi.';
            return;
        }

        if ($attendance->closed_at && $attendance->closed_at->lessThanOrEqualTo(now())) {
            $attendance->update([
                'is_open' => false,
            ]);

            $this->flashMessage = 'Sesi absensi sudah ditutup.';
            return;
        }

        $attendance->update([
            'is_open' => false,
            'closed_at' => now(),
        ]);

        $this->flashMessage = 'Sesi absensi berhasil ditutup lebih awal.';
        $this->dispatch('attendance-closed');
    }

    public function checkIn(int $attendanceId): void
    {
        $attendance = Attendance::query()
            ->findOrFail($attendanceId);

        abort_unless(
            in_array((int) $attendance->class_id, $this->studentClassIds(), true),
            403
        );

        $this->refreshAttendanceStatus($attendance);
        $attendance->refresh();

        if (! $attendance->opened_at || ! $attendance->closed_at) {
            $this->flashMessage = 'Sesi absensi belum memiliki jadwal yang lengkap.';
            return;
        }

        if ($attendance->opened_at->greaterThan(now())) {
            $this->flashMessage = 'Sesi absensi belum dibuka.';
            return;
        }

        if ($attendance->closed_at->lessThanOrEqualTo(now())) {
            $this->flashMessage = 'Sesi absensi sudah ditutup.';
            return;
        }

        if (! $attendance->isWithinOpenWindow()) {
            $this->flashMessage = 'Sesi absensi belum dibuka atau sudah ditutup.';
            return;
        }

        $record = AttendanceRecord::query()
            ->where('attendance_id', $attendance->id)
            ->where('student_id', auth()->id())
            ->first();

        if ($record?->status === 'hadir') {
            $this->flashMessage = 'Kamu sudah melakukan check-in absensi.';
            return;
        }

        if ($record?->status === 'izin') {
            $this->flashMessage = 'Status kamu sudah ditandai izin oleh asisten. Hubungi asisten jika perlu koreksi.';
            return;
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

        $this->flashMessage = 'Absensi berhasil. Status kamu tercatat hadir.';
        $this->dispatch('attendance-checked-in');
    }

    public function updateStudentStatus(int $attendanceId, int $studentId, string $status): void
    {
        abort_unless(in_array($status, ['hadir', 'izin', 'alpha'], true), 422);

        $attendance = Attendance::query()
            ->with('kelas')
            ->findOrFail($attendanceId);

        abort_unless($this->canManageClass($attendance->kelas), 403);

        AttendanceRecord::updateOrCreate(
            [
                'attendance_id' => $attendance->id,
                'student_id' => $studentId,
            ],
            [
                'status' => $status,
                'checked_at' => $status === 'alpha' ? null : now(),
            ]
        );

        $this->flashMessage = 'Status absensi mahasiswa berhasil diperbarui.';
        $this->dispatch('attendance-status-updated');
    }

    public function selectAttendance(int $attendanceId): void
    {
        $attendance = Attendance::query()
            ->with('kelas')
            ->findOrFail($attendanceId);

        if (auth()->user()->hasRole('asisten')) {
            abort_unless($this->canManageClass($attendance->kelas), 403);
        } else {
            abort_unless(
                in_array((int) $attendance->class_id, $this->studentClassIds(), true),
                403
            );
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
        $ids = collect(app(StudentAccessService::class)->classIdsForStudent(auth()->user()))
            ->map(fn ($id) => (int) $id);

        if ($this->classId) {
            $ids = $ids->filter(fn ($id) => (int) $id === (int) $this->classId);
        }

        return $ids
            ->unique()
            ->values()
            ->all();
    }

    private function canManageClass(?PraktikumClass $class): bool
    {
        return $class !== null
            && auth()->user()->hasRole('asisten')
            && (int) $class->assistant_id === (int) auth()->id();
    }

    private function refreshAttendanceStatus(Attendance $attendance): bool
    {
        if (method_exists($attendance, 'syncOpenStatus')) {
            return $attendance->syncOpenStatus();
        }

        $now = now();

        $shouldBeOpen = $attendance->opened_at
            && $attendance->closed_at
            && $attendance->opened_at->lessThanOrEqualTo($now)
            && $attendance->closed_at->greaterThan($now);

        if ((bool) $attendance->is_open === (bool) $shouldBeOpen) {
            return false;
        }

        $attendance->update([
            'is_open' => $shouldBeOpen,
        ]);

        return true;
    }

    private function refreshAttendanceCollection($attendances): void
    {
        $attendances->each(function (Attendance $attendance): void {
            $this->refreshAttendanceStatus($attendance);
        });
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
                ->latest('opened_at')
                ->latest('session_date')
                ->limit(10)
                ->get();

            $this->refreshAttendanceCollection($attendances);

            $selectedAttendance = $this->selectedAttendanceId
                ? Attendance::query()
                    ->with(['kelas.course', 'records.student'])
                    ->find($this->selectedAttendanceId)
                : $attendances->first();

            if ($selectedAttendance) {
                $this->refreshAttendanceStatus($selectedAttendance);
                $selectedAttendance->refresh();
                $selectedAttendance->loadMissing(['kelas.course', 'records.student']);
            }

            return view('livewire.absensi-live', [
                'mode' => 'asisten',
                'classes' => $classes,
                'attendances' => $attendances,
                'selectedAttendance' => $selectedAttendance,
                'activeAttendances' => collect(),
            ]);
        }

        $studentClassIds = $this->studentClassIds();

        $activeAttendances = Attendance::query()
            ->with([
                'kelas.course',
                'records' => fn ($query) => $query->where('student_id', auth()->id()),
            ])
            ->whereIn('class_id', $studentClassIds)
            ->whereNotNull('opened_at')
            ->whereNotNull('closed_at')
            ->where('opened_at', '<=', now())
            ->where('closed_at', '>', now())
            ->where('is_open', true)
            ->latest('opened_at')
            ->get();

        $attendances = Attendance::query()
            ->with([
                'kelas.course',
                'records' => fn ($query) => $query->where('student_id', auth()->id()),
            ])
            ->whereIn('class_id', $studentClassIds)
            ->latest('opened_at')
            ->latest('session_date')
            ->limit(10)
            ->get();

        $this->refreshAttendanceCollection($activeAttendances);
        $this->refreshAttendanceCollection($attendances);

        return view('livewire.absensi-live', [
            'mode' => 'mahasiswa',
            'classes' => collect(),
            'attendances' => $attendances,
            'selectedAttendance' => null,
            'activeAttendances' => $activeAttendances,
        ]);
    }
}