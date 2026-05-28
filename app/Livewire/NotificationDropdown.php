<?php

namespace App\Livewire;

use App\Models\LmsNotification;
use Livewire\Attributes\On;
use Livewire\Component;

class NotificationDropdown extends Component
{
    public bool $open = false;

    public int $limit = 8;

    #[On('notifikasi-baru')]
    public function refreshNotifications(): void
    {
        // Method ini sengaja kosong. Event Livewire akan memicu render ulang komponen.
    }

    public function toggle(): void
    {
        $this->open = ! $this->open;
    }

    public function close(): void
    {
        $this->open = false;
    }

    public function markAsRead(string $notificationId): void
    {
        $notification = LmsNotification::query()
            ->where('user_id', auth()->id())
            ->whereKey($notificationId)
            ->firstOrFail();

        $notification->update(['read_at' => now()]);
    }

    public function markAllAsRead(): void
    {
        LmsNotification::query()
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }

    public function deleteNotification(string $notificationId): void
    {
        LmsNotification::query()
            ->where('user_id', auth()->id())
            ->whereKey($notificationId)
            ->delete();
    }

    public function render()
    {
        $notifications = LmsNotification::query()
            ->where('user_id', auth()->id())
            ->latest()
            ->limit($this->limit)
            ->get();

        $unreadCount = LmsNotification::query()
            ->where('user_id', auth()->id())
            ->whereNull('read_at')
            ->count();

        return view('livewire.notification-dropdown', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }
}
