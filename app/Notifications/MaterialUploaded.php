<?php

namespace App\Notifications;

use App\Models\Material;
use App\Models\User;
use App\Notifications\Concerns\BuildsStudentNotificationUrls;

class MaterialUploaded extends LmsBaseNotification
{
    use BuildsStudentNotificationUrls;

    public function __construct(public int $materialId)
    {
        $this->onQueue('notifications');
    }

    public function payloadFor(User $notifiable): array
    {
        $material = Material::query()->with('kelas.course')->find($this->materialId);
        $className = $material?->kelas?->name ?? 'kelas praktikum';
        $courseName = $material?->kelas?->course?->name ?? 'praktikum';
        $title = $material?->title ?? 'Materi baru';

        return [
            'type' => 'material_uploaded',
            'title' => 'Materi Baru Diunggah',
            'message' => "Materi {$title} untuk {$className} ({$courseName}) sudah tersedia.",
            'data' => [
                'material_id' => $this->materialId,
                'class_id' => $material?->class_id,
                'course_id' => $material?->kelas?->course_id,
                'url' => $this->studentMaterialUrl($this->materialId),
            ],
        ];
    }
}
