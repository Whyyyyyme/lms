<?php

namespace App\Http\Controllers;

use App\Models\LmsNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(): View
    {
        $notifications = auth()->user()
            ->lmsNotifications()
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function markAsRead(LmsNotification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === auth()->id(), 403);

        $notification->update(['read_at' => now()]);

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        auth()->user()
            ->lmsNotifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi sudah dibaca.');
    }

    public function destroy(LmsNotification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === auth()->id(), 403);

        $notification->delete();

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }
}
