<?php

namespace App\Http\Controllers\Concerns;

use App\Models\LmsNotification;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

trait HandlesLmsNotifications
{
    /**
     * Buat notifikasi database sederhana yang kompatibel dengan tabel notifications LMS.
     * Notification class broadcast akan dibuat pada tahap berikutnya.
     */
    protected function notifyUsers(Collection $users, string $type, string $title, string $message, array $data = []): void
    {
        $users->filter()->unique('id')->each(function (User $user) use ($type, $title, $message, $data): void {
            LmsNotification::create([
                'id' => (string) Str::uuid(),
                'type' => $type,
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'user_id' => $user->id,
                'title' => $title,
                'message' => $message,
                'data' => $data,
                'read_at' => null,
            ]);
        });
    }
}
