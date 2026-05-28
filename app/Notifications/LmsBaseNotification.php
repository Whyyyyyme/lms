<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

abstract class LmsBaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Channel standar Laravel. Untuk kolom custom notifications.user_id/title/message,
     * gunakan App\Services\LmsNotificationService atau trait HandlesLmsNotifications.
     */
    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    /**
     * Payload tunggal yang dipakai database, broadcast, dan service LMS.
     *
     * @return array{type:string,title:string,message:string,data:array<string,mixed>}
     */
    abstract public function payloadFor(User $notifiable): array;

    public function toArray(object $notifiable): array
    {
        $payload = $this->payloadFor($this->resolveUser($notifiable));

        return [
            'type' => $payload['type'],
            'title' => $payload['title'],
            'message' => $payload['message'],
            'url' => $payload['data']['url'] ?? null,
            'data' => $payload['data'],
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        $payload = $this->payloadFor($this->resolveUser($notifiable));

        return new BroadcastMessage([
            'type' => $payload['type'],
            'title' => $payload['title'],
            'message' => $payload['message'],
            'data' => $payload['data'],
            'user_id' => $notifiable->id ?? null,
            'created_at' => now()->toIso8601String(),
        ]);
    }

    public function databaseType(object $notifiable): string
    {
        return $this->payloadFor($this->resolveUser($notifiable))['type'];
    }

    private function resolveUser(object $notifiable): User
    {
        if ($notifiable instanceof User) {
            return $notifiable;
        }

        /** @var User $user */
        $user = User::query()->findOrFail($notifiable->getKey());

        return $user;
    }
}
