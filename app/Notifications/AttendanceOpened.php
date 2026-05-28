<?php

namespace App\Notifications;

use App\Models\Attendance;
use App\Models\User;
use App\Notifications\Concerns\BuildsStudentNotificationUrls;

class AttendanceOpened extends LmsBaseNotification
{
    use BuildsStudentNotificationUrls;

    public function __construct(public int $attendanceId)
    {
        $this->onQueue('notifications');
    }

    public function payloadFor(User $notifiable): array
    {
        $attendance = Attendance::query()->with('kelas.course')->find($this->attendanceId);
        $className = $attendance?->kelas?->name ?? 'kelas praktikum';
        $sessionDate = $attendance?->session_date?->translatedFormat('d F Y') ?? now()->translatedFormat('d F Y');

        return [
            'type' => 'attendance_opened',
            'title' => 'Sesi Absensi Dibuka',
            'message' => "Absensi {$className} tanggal {$sessionDate} sudah dibuka. Silakan check-in sebelum ditutup.",
            'data' => [
                'attendance_id' => $this->attendanceId,
                'class_id' => $attendance?->class_id,
                'session_date' => $attendance?->session_date?->toDateString(),
                'url' => $this->studentAttendanceUrl(),
            ],
        ];
    }
}
