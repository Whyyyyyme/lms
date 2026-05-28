<?php

namespace App\Http\Controllers\Concerns;

use App\Models\User;
use App\Notifications\LmsBaseNotification;
use App\Services\LmsNotificationService;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

trait HandlesLmsNotifications
{
    /**
     * Kompatibel dengan controller lama: cukup panggil notifyUsers($students, ...).
     * Sekarang prosesnya masuk queue dan broadcast event setelah tersimpan.
     */
    protected function notifyUsers(Collection|EloquentCollection $users, string $type, string $title, string $message, array $data = []): void
    {
        app(LmsNotificationService::class)->sendRaw($users, $type, $title, $message, $data);
    }

    /**
     * Versi baru: gunakan class Notification agar payload lebih rapi dan reusable.
     */
    protected function notifyWithClass(User|Collection|EloquentCollection $users, LmsBaseNotification $notification): void
    {
        app(LmsNotificationService::class)->send($users, $notification);
    }
}
