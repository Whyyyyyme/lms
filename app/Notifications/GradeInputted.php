<?php

namespace App\Notifications;

use App\Models\Submission;
use App\Models\User;
use App\Notifications\Concerns\BuildsStudentNotificationUrls;

class GradeInputted extends LmsBaseNotification
{
    use BuildsStudentNotificationUrls;

    public function __construct(public int $submissionId)
    {
        $this->onQueue('notifications');
    }

    public function payloadFor(User $notifiable): array
    {
        $submission = Submission::query()->with('assignment')->find($this->submissionId);
        $assignment = $submission?->assignment;
        $score = $submission?->score !== null ? number_format((float) $submission->score, 2, ',', '.') : '-';
        $maxScore = $assignment?->max_score ?? 100;
        $assignmentTitle = $assignment?->title ?? 'tugas';

        return [
            'type' => 'grade_inputted',
            'title' => 'Nilai Sudah Diinput',
            'message' => "Nilai untuk {$assignmentTitle} sudah tersedia: {$score}/{$maxScore}.",
            'data' => [
                'submission_id' => $this->submissionId,
                'assignment_id' => $assignment?->id,
                'score' => $submission?->score,
                'max_score' => $maxScore,
                'url' => $this->studentGradeUrl(),
            ],
        ];
    }
}
