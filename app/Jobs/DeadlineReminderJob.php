<?php

namespace App\Jobs;

use App\Models\Assignment;
use App\Notifications\DeadlineReminder;
use App\Services\LmsNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeadlineReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $backoff = 30;

    /**
     * Reminder dikirim H-3 dan H-1 untuk mahasiswa yang belum submit.
     */
    public function handle(LmsNotificationService $notificationService): void
    {
        foreach ([3, 1] as $daysBefore) {
            $targetDate = now()->addDays($daysBefore)->toDateString();

            Assignment::query()
                ->with(['kelas.students', 'submissions'])
                ->whereDate('deadline', $targetDate)
                ->chunkById(50, function ($assignments) use ($daysBefore, $notificationService): void {
                    foreach ($assignments as $assignment) {
                        $students = $assignment->kelas?->students ?? collect();

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

                            $notificationService->send($student, new DeadlineReminder($assignment->id, $daysBefore));
                        }
                    }
                });
        }
    }
}
