<?php

namespace App\Notifications;

use App\Models\Assignment;
use App\Models\User;
use App\Notifications\Concerns\BuildsStudentNotificationUrls;
use Illuminate\Notifications\Messages\MailMessage;

class DeadlineReminder extends LmsBaseNotification
{
    use BuildsStudentNotificationUrls;

    public function __construct(
        public int $assignmentId,
        public int $amountBefore,
        public string $unit = 'day'
    ) {
        $this->onQueue('notifications');
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function payloadFor(User $notifiable): array
    {
        $assignment = Assignment::query()
            ->with('kelas.course')
            ->find($this->assignmentId);

        $deadline = $this->formattedDeadline($assignment);
        $label = $this->reminderLabel();
        $assignmentTitle = $assignment?->title ?? 'tugas';
        $courseName = $assignment?->kelas?->course?->name ?? 'mata kuliah';

        return [
            'type' => 'deadline_reminder',
            'title' => "Pengingat Deadline {$label}",
            'message' => "Tugas {$assignmentTitle} dari {$courseName} mendekati deadline ({$deadline}) dan belum kamu kumpulkan.",
            'data' => [
                'assignment_id' => $this->assignmentId,
                'class_id' => $assignment?->class_id,
                'course_id' => $assignment?->kelas?->course_id,
                'course_name' => $courseName,
                'class_name' => $assignment?->kelas?->name,
                'deadline' => $assignment?->deadline?->toDateTimeString(),
                'amount_before' => $this->amountBefore,
                'unit' => $this->unit,

                // Tetap disediakan untuk kompatibilitas job lama.
                'days_before' => $this->unit === 'day' ? $this->amountBefore : null,

                'url' => $this->studentAssignmentUrl($this->assignmentId),
            ],
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $assignment = Assignment::query()
            ->with('kelas.course')
            ->find($this->assignmentId);

        $assignmentTitle = $assignment?->title ?? 'Tugas';
        $courseName = $assignment?->kelas?->course?->name ?? 'Mata kuliah';
        $className = $assignment?->kelas?->name ?? 'Kelas praktikum';
        $deadline = $this->formattedDeadline($assignment);
        $label = $this->reminderLabel();
        $url = $this->studentAssignmentUrl($this->assignmentId);

        return (new MailMessage)
            ->subject("Pengingat Deadline Tugas {$label}")
            ->greeting('Halo ' . ($notifiable->name ?? 'Mahasiswa') . ',')
            ->line("Tugas dari mata kuliah {$courseName} belum kamu kumpulkan.")
            ->line("Judul tugas: {$assignmentTitle}")
            ->line("Kelas: {$className}")
            ->line("Deadline: {$deadline}")
            ->line('Segera buka LMS dan kumpulkan tugas sebelum deadline berakhir.')
            ->action('Buka Tugas di LMS', $url)
            ->line('Abaikan email ini jika kamu sudah mengumpulkan tugas.');
    }

    public function toArray(object $notifiable): array
    {
        if ($notifiable instanceof User) {
            return $this->payloadFor($notifiable);
        }

        return [
            'type' => 'deadline_reminder',
            'title' => 'Pengingat Deadline Tugas',
            'message' => 'Ada tugas yang mendekati deadline dan belum dikumpulkan.',
            'data' => [
                'assignment_id' => $this->assignmentId,
                'amount_before' => $this->amountBefore,
                'unit' => $this->unit,
            ],
        ];
    }

    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }

    private function reminderLabel(): string
    {
        if ($this->unit === 'minute') {
            return $this->amountBefore . ' menit sebelum deadline';
        }

        if ($this->unit === 'hour') {
            return $this->amountBefore . ' jam sebelum deadline';
        }

        return $this->amountBefore === 1
            ? 'H-1'
            : 'H-' . $this->amountBefore;
    }

    private function formattedDeadline(?Assignment $assignment): string
    {
        if (! $assignment?->deadline) {
            return '-';
        }

        return $assignment->deadline
            ->timezone(config('app.timezone', 'Asia/Jakarta'))
            ->translatedFormat('d F Y H:i') . ' WIB';
    }
}