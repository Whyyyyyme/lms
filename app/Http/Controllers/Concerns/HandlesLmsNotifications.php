<?php

namespace App\Http\Controllers\Concerns;

use App\Services\LmsNotificationService;
use Illuminate\Support\Collection;

trait HandlesLmsNotifications
{
    /**
     * Semua controller/job memakai pintu yang sama untuk notifikasi LMS.
     *
     * Sebelumnya trait ini langsung create ke tabel notifications, sehingga
     * alurnya berbeda dari LmsNotificationService. Sekarang semua diarahkan
     * ke service agar queue, email, broadcast, dan anti-duplikasi konsisten.
     */
    protected function notifyUsers(Collection $users, string $type, string $title, string $message, array $data = []): void
    {
        app(LmsNotificationService::class)->sendRaw(
            users: $users,
            type: $type,
            title: $title,
            message: $message,
            data: $data,
        );
    }

    protected function classContext($class): array
    {
        if ($class && method_exists($class, 'loadMissing')) {
            $class->loadMissing('course');
        }

        $courseName = $class?->course?->name ?? 'Mata kuliah tidak diketahui';
        $courseCode = $class?->course?->code ?? null;
        $className = $class?->name ?? 'Kelas tidak diketahui';

        return [
            'course_name' => $courseName,
            'course_code' => $courseCode,
            'class_name' => $className,
            'label' => trim(($courseCode ? "{$courseCode} - " : '') . "{$courseName} - {$className}"),
        ];
    }
}
