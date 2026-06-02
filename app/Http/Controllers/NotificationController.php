<?php

namespace App\Http\Controllers;

use App\Models\LmsNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Route;
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

    public function open(LmsNotification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === auth()->id(), 403);

        if (is_null($notification->read_at)) {
            $notification->update([
                'read_at' => now(),
            ]);
        }

        $data = is_array($notification->data) ? $notification->data : [];

        $url = data_get($data, 'url');

        if (filled($url)) {
            return redirect()->to($this->safeRedirectUrl($url));
        }

        $type = strtolower((string) $notification->type);
        $title = strtolower((string) $notification->title);
        $message = strtolower((string) $notification->message);

        if (
            str_contains($type, 'attendance') ||
            str_contains($type, 'absensi') ||
            str_contains($title, 'absensi') ||
            str_contains($message, 'absensi')
        ) {
            return redirect()->to($this->routeUrl('student.attendances.index', [], '/mahasiswa/absensi'));
        }

        if (
            str_contains($type, 'assignment') ||
            str_contains($type, 'tugas') ||
            str_contains($title, 'tugas') ||
            str_contains($message, 'tugas')
        ) {
            $assignmentId = data_get($data, 'assignment_id');

            if ($assignmentId && Route::has('student.assignments.show')) {
                return redirect()->route('student.assignments.show', $assignmentId);
            }

            return redirect()->to($this->routeUrl('student.assignments.index', [], '/mahasiswa/tugas'));
        }

        if (
            str_contains($type, 'material') ||
            str_contains($type, 'materi') ||
            str_contains($title, 'materi') ||
            str_contains($message, 'materi')
        ) {
            $materialId = data_get($data, 'material_id');

            if ($materialId && Route::has('student.materials.show')) {
                return redirect()->route('student.materials.show', $materialId);
            }

            return redirect()->to($this->routeUrl('student.materials.index', [], '/mahasiswa/materi'));
        }

        if (
            str_contains($type, 'grade') ||
            str_contains($type, 'nilai') ||
            str_contains($title, 'nilai') ||
            str_contains($message, 'nilai')
        ) {
            return redirect()->to($this->routeUrl('student.grades.index', [], '/mahasiswa/nilai'));
        }

        if (
            str_contains($type, 'announcement') ||
            str_contains($type, 'pengumuman') ||
            str_contains($title, 'pengumuman') ||
            str_contains($message, 'pengumuman')
        ) {
            return redirect()->to($this->routeUrl('student.dashboard', [], '/mahasiswa/dashboard'));
        }

        return redirect()->route('notifications.index');
    }

    public function markAsRead(LmsNotification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === auth()->id(), 403);

        $notification->update([
            'read_at' => now(),
        ]);

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllAsRead(): RedirectResponse
    {
        auth()->user()
            ->lmsNotifications()
            ->whereNull('read_at')
            ->update([
                'read_at' => now(),
            ]);

        return back()->with('success', 'Semua notifikasi sudah dibaca.');
    }

    public function destroy(LmsNotification $notification): RedirectResponse
    {
        abort_unless((int) $notification->user_id === auth()->id(), 403);

        $notification->delete();

        return back()->with('success', 'Notifikasi berhasil dihapus.');
    }

    private function routeUrl(string $routeName, array $parameters = [], string $fallbackPath = '/notifikasi'): string
    {
        if (Route::has($routeName)) {
            return route($routeName, $parameters);
        }

        return url($fallbackPath);
    }

    private function safeRedirectUrl(string $url): string
    {
        $parsedUrl = parse_url($url);

        if (! empty($parsedUrl['host']) && $parsedUrl['host'] !== request()->getHost()) {
            return route('notifications.index');
        }

        return $url;
    }
}
