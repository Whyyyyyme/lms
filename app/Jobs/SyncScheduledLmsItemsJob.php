<?php

namespace App\Jobs;

use App\Http\Controllers\Concerns\HandlesLmsNotifications;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\Material;
use App\Models\PraktikumClass;
use App\Services\StudentAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class SyncScheduledLmsItemsJob
{
    use Dispatchable, HandlesLmsNotifications;

    public function handle(StudentAccessService $studentAccess): void
    {
        $this->publishScheduledMaterials($studentAccess);
        $this->publishScheduledAssignments($studentAccess);
        $this->openScheduledAttendances($studentAccess);
        $this->closeExpiredAttendances();
    }

    private function publishScheduledMaterials(StudentAccessService $studentAccess): void
    {
        if (! Schema::hasColumn('materials', 'published_notification_sent_at')) {
            return;
        }

        $query = Material::query()
            ->with(['kelas.course.studySemester', 'kelas.course.academicYear'])
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->whereNull('published_notification_sent_at');

        $this->applyActiveAcademicYearFilter($query);

        $query->chunkById(50, function ($materials) use ($studentAccess): void {
            foreach ($materials as $material) {
                $class = $material->kelas;

                if (! $class instanceof PraktikumClass) {
                    $material->update([
                        'published_notification_sent_at' => now(),
                    ]);

                    continue;
                }

                if (! $this->classBelongsToActiveAcademicYear($class)) {
                    continue;
                }

                $classInfo = $this->classContext($class);

                $this->notifyUsers(
                    $this->studentsForClass($studentAccess, $class),
                    'material_uploaded',
                    'Materi Baru Diunggah',
                    "{$material->title} telah tersedia untuk {$classInfo['label']}.",
                    [
                        'material_id' => $material->id,
                        'class_id' => $class->id,
                        'course_name' => $classInfo['course_name'],
                        'course_code' => $classInfo['course_code'],
                        'class_name' => $classInfo['class_name'],
                        'context_label' => $classInfo['label'],
                        'url' => route('student.materials.show', $material),
                    ]
                );

                $material->update([
                    'published_notification_sent_at' => now(),
                ]);
            }
        });
    }

    private function publishScheduledAssignments(StudentAccessService $studentAccess): void
    {
        if (! Schema::hasColumn('assignments', 'published_notification_sent_at')) {
            return;
        }

        $query = Assignment::query()
            ->with(['kelas.course.studySemester', 'kelas.course.academicYear'])
            ->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->whereNull('published_notification_sent_at');

        $this->applyActiveAcademicYearFilter($query);

        $query->chunkById(50, function ($assignments) use ($studentAccess): void {
            foreach ($assignments as $assignment) {
                $class = $assignment->kelas;

                if (! $class instanceof PraktikumClass) {
                    $assignment->update([
                        'published_notification_sent_at' => now(),
                    ]);

                    continue;
                }

                if (! $this->classBelongsToActiveAcademicYear($class)) {
                    continue;
                }

                $classInfo = $this->classContext($class);

                $this->notifyUsers(
                    $this->studentsForClass($studentAccess, $class),
                    'assignment_created',
                    'Tugas Baru',
                    "{$assignment->title} telah dibuat untuk {$classInfo['label']}.",
                    [
                        'assignment_id' => $assignment->id,
                        'class_id' => $class->id,
                        'course_id' => $class->course_id,
                        'course_name' => $classInfo['course_name'],
                        'course_code' => $classInfo['course_code'],
                        'class_name' => $classInfo['class_name'],
                        'context_label' => $classInfo['label'],
                        'deadline' => $assignment->deadline?->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y H:i') . ' WIB',
                        'url' => route('student.assignments.show', $assignment),
                    ]
                );

                $assignment->update([
                    'published_notification_sent_at' => now(),
                ]);
            }
        });
    }

    private function openScheduledAttendances(StudentAccessService $studentAccess): void
    {
        if (! Schema::hasColumn('attendances', 'opened_notification_sent_at')) {
            return;
        }

        $now = now();

        $query = Attendance::query()
            ->with(['kelas.course.studySemester', 'kelas.course.academicYear'])
            ->whereNotNull('opened_at')
            ->whereNotNull('closed_at')
            ->where('opened_at', '<=', $now)
            ->where('closed_at', '>', $now);

        $this->applyActiveAcademicYearFilter($query);

        $query->chunkById(50, function ($attendances) use ($studentAccess): void {
            foreach ($attendances as $attendance) {
                $class = $attendance->kelas;

                if (! $class instanceof PraktikumClass) {
                    continue;
                }

                if (! $this->classBelongsToActiveAcademicYear($class)) {
                    continue;
                }

                Attendance::query()
                    ->where('class_id', $attendance->class_id)
                    ->whereKeyNot($attendance->id)
                    ->where('is_open', true)
                    ->update([
                        'is_open' => false,
                        'closed_notification_sent_at' => now(),
                    ]);

                if (! $attendance->is_open) {
                    $attendance->forceFill([
                        'is_open' => true,
                    ])->save();
                }

                $this->syncAttendanceRecords($studentAccess, $attendance);
                $attendance->refresh();

                if ($attendance->opened_notification_sent_at) {
                    continue;
                }

                $classInfo = $this->classContext($class);

                $this->notifyUsers(
                    $this->studentsForClass($studentAccess, $class),
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

                $attendance->update([
                    'opened_notification_sent_at' => now(),
                ]);
            }
        });
    }

    private function closeExpiredAttendances(): void
    {
        if (! Schema::hasColumn('attendances', 'closed_notification_sent_at')) {
            return;
        }

        $query = Attendance::query()
            ->with(['kelas.course.academicYear'])
            ->where('is_open', true)
            ->whereNotNull('closed_at')
            ->where('closed_at', '<=', now());

        $this->applyActiveAcademicYearFilter($query);

        $query->chunkById(50, function ($attendances): void {
            foreach ($attendances as $attendance) {
                $attendance->update([
                    'is_open' => false,
                    'closed_notification_sent_at' => $attendance->closed_notification_sent_at ?? now(),
                ]);
            }
        });
    }

    private function syncAttendanceRecords(StudentAccessService $studentAccess, Attendance $attendance): void
    {
        $class = $attendance->kelas;

        if (! $class instanceof PraktikumClass) {
            return;
        }

        if (! $this->classBelongsToActiveAcademicYear($class)) {
            return;
        }

        foreach ($this->studentsForClass($studentAccess, $class) as $student) {
            AttendanceRecord::firstOrCreate([
                'attendance_id' => $attendance->id,
                'student_id' => $student->id,
            ], [
                'status' => 'alpha',
                'checked_at' => null,
            ]);
        }
    }

    private function studentsForClass(StudentAccessService $studentAccess, PraktikumClass $class): Collection
    {
        if (! $this->classBelongsToActiveAcademicYear($class)) {
            return collect();
        }

        return $studentAccess->studentsForClass($class)
            ->filter(fn ($student) => (bool) $student->is_active)
            ->unique('id')
            ->values();
    }

    /**
     * Scheduler publikasi/notifikasi hanya berjalan untuk kelas dari tahun akademik aktif.
     * Kelas dari tahun akademik nonaktif diperlakukan sebagai riwayat, sehingga tidak
     * membuka absensi, membuat record alpha, atau mengirim notifikasi baru.
     */
    private function applyActiveAcademicYearFilter(Builder $query): void
    {
        $query->whereHas('kelas', function (Builder $classQuery): void {
            $classQuery
                ->where('is_active', true)
                ->whereHas('course', function (Builder $courseQuery): void {
                    $courseQuery
                        ->where('is_active', true)
                        ->where(function (Builder $academicYearQuery): void {
                            $academicYearQuery
                                ->whereHas('academicYear', function (Builder $query): void {
                                    $query->where('is_active', true);
                                })
                                // Data lama yang belum punya tahun akademik tetap diproses
                                // agar tidak langsung rusak setelah update kode.
                                ->orWhereDoesntHave('academicYear');
                        });
                });
        });
    }

    private function classBelongsToActiveAcademicYear(PraktikumClass $class): bool
    {
        $class->loadMissing('course.academicYear');

        if (! (bool) $class->is_active) {
            return false;
        }

        $course = $class->course;

        if (! $course || ! (bool) $course->is_active) {
            return false;
        }

        $academicYear = $course->academicYear;

        return $academicYear === null || (bool) $academicYear->is_active;
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
