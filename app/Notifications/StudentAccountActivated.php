<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentAccountActivated extends Notification
{
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Akun LMS Praktikum Anda Sudah Aktif')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Akun LMS Praktikum Anda sudah diverifikasi dan diaktifkan oleh admin.')
            ->line('Sekarang Anda bisa login menggunakan email dan password LMS yang Anda buat saat registrasi.')
            ->action('Login ke LMS Praktikum', route('login'))
            ->line('Jika Anda tidak merasa mendaftar akun LMS Praktikum, abaikan email ini.');
    }
}