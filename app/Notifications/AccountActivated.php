<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountActivated extends Notification
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Akun LMS Praktikum Kamu Sudah Aktif')
            ->greeting('Halo, ' . ($notifiable->name ?? 'Mahasiswa') . '!')
            ->line('Akun LMS Praktikum kamu sudah diverifikasi dan diaktifkan oleh admin.')
            ->line('Sekarang kamu bisa login menggunakan email dan password yang kamu buat saat registrasi.')
            ->action('Login ke LMS Praktikum', route('login'))
            ->line('Jika kamu tidak merasa mendaftar, abaikan email ini atau hubungi admin LMS.');
    }
}
