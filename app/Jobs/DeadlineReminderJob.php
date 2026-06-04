<?php

namespace App\Jobs;

use App\Models\Assignment;
use App\Notifications\DeadlineReminder;
use App\Services\LmsNotificationService;
use App\Services\StudentAccessService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;

class DeadlineReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $backoff = 30;

    /**
     * Reminder dikirim H-3 dan H-1 untuk mahasiswa yang belum submit.
     */
    public function handle(LmsNotificationService $notificationService, StudentAccessService $studentAccess): void
    {
        foreach ([3, 1] as $daysBefore) {
            $targetDate = now()->addDays($daysBefore)->toDateString();

            $query = Assignment::query()
                ->with([
                    'kelas.course.studySemester',
                    'kelas.students.studySemester',
                    'submissions',
                ])
                ->whereNotNull('deadline')
                ->whereDate('deadline', $targetDate);

            $this->applyPublishedFilter($query);

            $query->chunkById(50, function ($assignments) use ($daysBefore, $notificationService, $studentAccess): void {
                foreach ($assignments as $assignment) {
                    if (! $assignment->kelas) {
                        continue;
                    }

                    $students = $studentAccess->studentsForClass($assignment->kelas);

                    foreach ($students as $student) {
                        $alreadySubmitted = $assignment->submissions
                            ->where('student_id', $student->id)
                            ->isNotEmpty();

                        if ($alreadySubmitted) {
                            continue;
                        }

                        $alreadySent = $notificationService->alreadySentToday($student, 'deadline_reminder', [
                            'assignment_id' => $assignment->id,
                            'days_before' => $daysBefore,
                        ]);

                        if ($alreadySent) {
                            continue;
                        }

                        $notificationService->send(
                            $student,
                            new DeadlineReminder($assignment->id, $daysBefore)
                        );
                    }
                }
            });
        }
    }

    private function applyPublishedFilter(Builder $query): void
    {
        if (Schema::hasColumn('assignments', 'is_published')) {
            $query->where('is_published', true);

            return;
        }

        if (Schema::hasColumn('assignments', 'status')) {
            $query->where('status', 'published');
        }
    }
}