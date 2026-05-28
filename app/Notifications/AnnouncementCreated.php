<?php

namespace App\Notifications;

use App\Models\Announcement;
use App\Models\User;
use App\Notifications\Concerns\BuildsStudentNotificationUrls;

class AnnouncementCreated extends LmsBaseNotification
{
    use BuildsStudentNotificationUrls;

    public function __construct(public int $announcementId)
    {
        $this->onQueue('notifications');
    }

    public function payloadFor(User $notifiable): array
    {
        $announcement = Announcement::query()->with('kelas.course')->find($this->announcementId);
        $title = $announcement?->title ?? 'Pengumuman baru';
        $className = $announcement?->kelas?->name ?? 'kelas praktikum';

        return [
            'type' => 'announcement_created',
            'title' => 'Pengumuman Baru',
            'message' => "Pengumuman {$title} untuk {$className} sudah diterbitkan.",
            'data' => [
                'announcement_id' => $this->announcementId,
                'class_id' => $announcement?->class_id,
                'url' => $this->studentDashboardUrl(),
            ],
        ];
    }
}
