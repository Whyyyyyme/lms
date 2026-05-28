<?php

namespace App\Notifications;

use App\Models\Assignment;
use App\Models\User;
use App\Notifications\Concerns\BuildsStudentNotificationUrls;

class AssignmentCreated extends LmsBaseNotification
{
    use BuildsStudentNotificationUrls;

    public function __construct(public int $assignmentId)
    {
        $this->onQueue('notifications');
    }

    public function payloadFor(User $notifiable): array
    {
        $assignment = Assignment::query()->with('kelas.course')->find($this->assignmentId);
        $deadline = $assignment?->deadline?->translatedFormat('d F Y H:i') ?? '-';
        $className = $assignment?->kelas?->name ?? 'kelas praktikum';
        $title = $assignment?->title ?? 'Tugas baru';

        return [
            'type' => 'assignment_created',
            'title' => 'Tugas Baru Dibuat',
            'message' => "Tugas {$title} untuk {$className} sudah tersedia. Deadline: {$deadline}.",
            'data' => [
                'assignment_id' => $this->assignmentId,
                'class_id' => $assignment?->class_id,
                'deadline' => $assignment?->deadline?->toDateTimeString(),
                'url' => $this->studentAssignmentUrl($this->assignmentId),
            ],
        ];
    }
}
