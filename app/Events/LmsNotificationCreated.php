<?php

namespace App\Events;

use App\Models\LmsNotification;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LmsNotificationCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public LmsNotification $notification)
    {
    }

    public function broadcastOn(): Channel
    {
        return new PrivateChannel('lms.notifications.' . $this->notification->user_id);
    }

    public function broadcastAs(): string
    {
        return 'notifikasi-baru';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,
            'title' => $this->notification->title,
            'message' => $this->notification->message,
            'data' => $this->notification->data,
            'read_at' => $this->notification->read_at?->toDateTimeString(),
            'created_at' => $this->notification->created_at?->toIso8601String(),
        ];
    }
}
