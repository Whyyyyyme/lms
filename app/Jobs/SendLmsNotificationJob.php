<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\LmsNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendLmsNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(
        public int $userId,
        public string $type,
        public string $title,
        public string $message,
        public array $data = [],
    ) {
    }

    public function handle(LmsNotificationService $notificationService): void
    {
        $user = User::query()
            ->whereKey($this->userId)
            ->where('is_active', true)
            ->first();

        if (! $user) {
            return;
        }

        $notificationService->storeNow(
            user: $user,
            type: $this->type,
            title: $this->title,
            message: $this->message,
            data: $this->data,
        );
    }
}
