<?php

namespace App\Services;

use App\Events\LmsNotificationCreated;
use App\Jobs\SendLmsNotificationJob;
use App\Models\LmsNotification;
use App\Models\User;
use App\Notifications\LmsBaseNotification;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class LmsNotificationService
{
    /**
     * Kirim notifikasi berbasis Notification class ke satu user atau banyak user.
     * Data akan dibuat lewat queue agar request controller tetap ringan.
     */
    public function send(User|Collection|EloquentCollection $users, LmsBaseNotification $notification): void
    {
        $this->normalizeUsers($users)->each(function (User $user) use ($notification): void {
            $payload = $notification->payloadFor($user);

            SendLmsNotificationJob::dispatch(
                userId: $user->id,
                type: $payload['type'],
                title: $payload['title'],
                message: $payload['message'],
                data: $payload['data'] ?? [],
            )->onQueue('notifications');
        });
    }

    /**
     * Kirim notifikasi mentah. Cocok untuk controller lama yang sudah memakai trait notifyUsers().
     */
    public function sendRaw(User|Collection|EloquentCollection $users, string $type, string $title, string $message, array $data = []): void
    {
        $this->normalizeUsers($users)->each(function (User $user) use ($type, $title, $message, $data): void {
            SendLmsNotificationJob::dispatch($user->id, $type, $title, $message, $data)
                ->onQueue('notifications');
        });
    }

    /**
     * Simpan notifikasi sekarang juga. Dipakai dari SendLmsNotificationJob.
     */
    public function storeNow(User $user, string $type, string $title, string $message, array $data = []): LmsNotification
    {
        $notification = LmsNotification::create([
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

        broadcast(new LmsNotificationCreated($notification))->toOthers();

        return $notification;
    }

    /**
     * Cegah notifikasi deadline dobel jika scheduler/queue sempat dijalankan ulang pada hari yang sama.
     */
    public function alreadySentToday(User $user, string $type, array $dataMatch = []): bool
    {
        $query = LmsNotification::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->whereDate('created_at', today());

        foreach ($dataMatch as $key => $value) {
            $query->where("data->{$key}", $value);
        }

        return $query->exists();
    }

    private function normalizeUsers(User|Collection|EloquentCollection $users): Collection
    {
        if ($users instanceof User) {
            return collect([$users]);
        }

        return collect($users)
            ->filter(fn ($user) => $user instanceof User)
            ->unique('id')
            ->values();
    }
}
