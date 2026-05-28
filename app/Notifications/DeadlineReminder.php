<?php

namespace App\Notifications;

use App\Models\Assignment;
use App\Models\User;
use App\Notifications\Concerns\BuildsStudentNotificationUrls;

class DeadlineReminder extends LmsBaseNotification
{
    use BuildsStudentNotificationUrls;

    public function __construct(public int $assignmentId, public int $daysBefore)
    {
        $this->onQueue('notifications');
    }

    public function payloadFor(User $notifiable): array
    {
        $assignment = Assignment::query()->with('kelas.course')->find($this->assignmentId);
        $deadline = $assignment?->deadline?->translatedFormat('d F Y H:i') ?? '-';
        $label = $this->daysBefore === 1 ? 'H-1' : 'H-' . $this->daysBefore;
        $assignmentTitle = $assignment?->title ?? 'tugas';

        return [
            'type' => 'deadline_reminder',
            'title' => "Pengingat Deadline {$label}",
            'message' => "Tugas {$assignmentTitle} mendekati deadline ({$deadline}) dan belum kamu kumpulkan.",
            'data' => [
                'assignment_id' => $this->assignmentId,
                'class_id' => $assignment?->class_id,
                'deadline' => $assignment?->deadline?->toDateTimeString(),
                'days_before' => $this->daysBefore,
                'url' => $this->studentAssignmentUrl($this->assignmentId),
            ],
        ];
    }
}
