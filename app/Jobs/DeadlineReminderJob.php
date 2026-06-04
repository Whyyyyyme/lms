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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class DeadlineReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 2;

    public int $backoff = 30;

    /**
     * Mode reminder:
     *
     * Testing:
     * DEADLINE_REMINDER_MODE=minutes
     * DEADLINE_REMINDER_AMOUNTS=3,2
     *
     * Production:
     * DEADLINE_REMINDER_MODE=days
     * DEADLINE_REMINDER_AMOUNTS=3,2
     */
    public function handle(LmsNotificationService $notificationService, StudentAccessService $studentAccess): void
    {
        $mode = $this->reminderMode();
        $amounts = $this->reminderAmounts();

        foreach ($amounts as $amountBefore) {
            $query = Assignment::query()
                ->with([
                    'kelas.course.studySemester',
                    'kelas.students.studySemester',
                    'submissions',
                ])
                ->whereNotNull('deadline');

            $this->applyPublishedFilter($query);
            $this->applyDeadlineTargetFilter($query, $amountBefore, $mode);

            $query->chunkById(50, function ($assignments) use ($amountBefore, $mode, $notificationService, $studentAccess): void {
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
                            'amount_before' => $amountBefore,
                            'unit' => $mode === 'minutes' ? 'minute' : 'day',

                            // Kompatibilitas untuk data lama.
                            'days_before' => $mode === 'days' ? $amountBefore : null,
                        ]);

                        if ($alreadySent) {
                            continue;
                        }

                        $notificationService->send(
                            $student,
                            new DeadlineReminder(
                                $assignment->id,
                                $amountBefore,
                                $mode === 'minutes' ? 'minute' : 'day'
                            )
                        );
                    }
                }
            });
        }
    }

    private function applyDeadlineTargetFilter(Builder $query, int $amountBefore, string $mode): void
    {
        $now = now();

        if ($mode === 'minutes') {
            $targetStart = $now->copy()->addMinutes($amountBefore)->startOfMinute();
            $targetEnd = $now->copy()->addMinutes($amountBefore)->endOfMinute();

            $query->whereBetween('deadline', [$targetStart, $targetEnd]);

            return;
        }

        $targetDate = $now->copy()->addDays($amountBefore)->toDateString();

        $query->whereDate('deadline', $targetDate);
    }

    private function applyPublishedFilter(Builder $query): void
    {
        if (Schema::hasColumn('assignments', 'published_at')) {
            $query->where(function ($query) {
                $query->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
        }

        if (Schema::hasColumn('assignments', 'is_published')) {
            $query->where('is_published', true);

            return;
        }

        if (Schema::hasColumn('assignments', 'status')) {
            $query->where('status', 'published');
        }
    }

    private function reminderMode(): string
    {
        $mode = strtolower((string) env('DEADLINE_REMINDER_MODE', 'days'));

        return in_array($mode, ['minutes', 'days'], true)
            ? $mode
            : 'days';
    }

    /**
     * @return array<int>
     */
    private function reminderAmounts(): array
    {
        $rawAmounts = (string) env('DEADLINE_REMINDER_AMOUNTS', '3,2');

        $amounts = collect(explode(',', $rawAmounts))
            ->map(fn ($amount) => (int) trim($amount))
            ->filter(fn ($amount) => $amount > 0)
            ->unique()
            ->values()
            ->all();

        return $amounts !== [] ? $amounts : [3, 2];
    }
}