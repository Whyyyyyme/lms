<?php

namespace App\Services;

use App\Events\LmsNotificationCreated;
use App\Jobs\SendLmsNotificationJob;
use App\Models\LmsNotification;
use App\Models\User;
use App\Notifications\LmsBaseNotification;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Str;
use Throwable;

class LmsNotificationService
{
    /**
     * Kirim notifikasi LMS berbasis Notification class.
     *
     * Alur standar sekarang:
     * 1. In-app notification selalu memakai tabel custom notifications LMS.
     * 2. Email dikirim hanya lewat channel mail, bukan database/broadcast Laravel,
     *    agar tidak membuat baris notifikasi kosong/duplikat di laporan aktivitas.
     * 3. Queue in-app opsional. Default direct/sync agar aman di shared hosting.
     */
    public function send(User|Collection|EloquentCollection $users, LmsBaseNotification $notification): void
    {
        $this->normalizeUsers($users)->each(function (User $user) use ($notification): void {
            $payload = $notification->payloadFor($user);

            $this->sendRaw(
                users: $user,
                type: (string) $payload['type'],
                title: (string) $payload['title'],
                message: (string) $payload['message'],
                data: $payload['data'] ?? [],
            );

            $this->sendMailNotificationIfSupported($user, $notification);
        });
    }

    /**
     * Kirim notifikasi mentah untuk controller/job yang belum memakai Notification class.
     */
    public function sendRaw(User|Collection|EloquentCollection $users, string $type, string $title, string $message, array $data = []): void
    {
        $type = $this->normalizeText($type, 'general');
        $title = $this->normalizeText($title, Str::headline(str_replace(['_', '-'], ' ', $type)));
        $message = $this->normalizeText($message, $title);
        $data = $this->withDedupeKey($type, $data);

        $this->normalizeUsers($users)->each(function (User $user) use ($type, $title, $message, $data): void {
            if ($this->shouldQueueInAppNotifications()) {
                SendLmsNotificationJob::dispatch(
                    userId: $user->id,
                    type: $type,
                    title: $title,
                    message: $message,
                    data: $data,
                )->onQueue((string) config('lms-notifications.queue_name', 'notifications'));

                return;
            }

            $this->storeNow(
                user: $user,
                type: $type,
                title: $title,
                message: $message,
                data: $data,
            );
        });
    }

    /**
     * Simpan notifikasi sekarang juga. Dipakai juga oleh SendLmsNotificationJob.
     */
    public function storeNow(User $user, string $type, string $title, string $message, array $data = []): LmsNotification
    {
        $type = $this->normalizeText($type, 'general');
        $title = $this->normalizeText($title, Str::headline(str_replace(['_', '-'], ' ', $type)));
        $message = $this->normalizeText($message, $title);
        $data = $this->withDedupeKey($type, $data);

        $existing = $this->findExistingNotification($user, $type, $data);

        if ($existing instanceof LmsNotification) {
            return $existing;
        }

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

        if ($this->shouldBroadcast()) {
            broadcast(new LmsNotificationCreated($notification))->toOthers();
        }

        return $notification;
    }

    /**
     * Cegah notifikasi deadline dobel jika scheduler/queue dijalankan ulang.
     */
    public function alreadySentToday(User $user, string $type, array $dataMatch = []): bool
    {
        $query = LmsNotification::query()
            ->lmsRows()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->whereDate('created_at', today());

        foreach ($dataMatch as $key => $value) {
            if ($value === null) {
                continue;
            }

            $query->where("data->{$key}", $value);
        }

        return $query->exists();
    }

    private function sendMailNotificationIfSupported(User $user, LmsBaseNotification $notification): void
    {
        if (! (bool) config('lms-notifications.mail_enabled', true)) {
            return;
        }

        if (! $notification instanceof BaseNotification) {
            return;
        }

        if (! method_exists($notification, 'toMail')) {
            return;
        }

        if (blank($user->email)) {
            return;
        }

        try {
            /**
             * Penting: kirim channel mail saja.
             * Jangan panggil $user->notify($notification), karena via() pada beberapa
             * notification bisa ikut menulis database Laravel dan membuat data dobel
             * pada tabel notifications custom LMS.
             */
            NotificationFacade::sendNow($user, $notification, ['mail']);
        } catch (Throwable $exception) {
            report($exception);
        }
    }

    private function normalizeUsers(User|Collection|EloquentCollection $users): Collection
    {
        if ($users instanceof User) {
            return collect([$users]);
        }

        return collect($users)
            ->filter(fn ($user) => $user instanceof User)
            ->filter(fn (User $user) => (bool) $user->is_active)
            ->unique('id')
            ->values();
    }

    private function shouldQueueInAppNotifications(): bool
    {
        if (config('queue.default') === 'sync') {
            return false;
        }

        return (bool) config('lms-notifications.queue_in_app', false);
    }

    private function shouldBroadcast(): bool
    {
        if (! (bool) config('lms-notifications.broadcast_enabled', false)) {
            return false;
        }

        return ! in_array(config('broadcasting.default'), ['null', 'log'], true);
    }

    private function normalizeText(string $value, string $fallback): string
    {
        $value = trim($value);

        return $value !== '' ? $value : $fallback;
    }

    private function withDedupeKey(string $type, array $data): array
    {
        if (filled($data['dedupe_key'] ?? null)) {
            return $data;
        }

        $dedupeKey = $this->dedupeKeyFor($type, $data);

        if ($dedupeKey === null) {
            return $data;
        }

        $data['dedupe_key'] = $dedupeKey;

        return $data;
    }

    private function dedupeKeyFor(string $type, array $data): ?string
    {
        // Nilai bisa berubah karena regrade, jadi jangan dedupe notifikasi nilai.
        if (str_contains($type, 'grade') || str_contains($type, 'nilai')) {
            return null;
        }

        $keys = [
            'announcement_id',
            'assignment_id',
            'attendance_id',
            'material_id',
            'submission_id',
            'class_id',
            'amount_before',
            'unit',
            'days_before',
        ];

        $parts = ['type' => $type];

        foreach ($keys as $key) {
            if (array_key_exists($key, $data) && $data[$key] !== null && $data[$key] !== '') {
                $parts[$key] = $data[$key];
            }
        }

        if (count($parts) === 1) {
            return null;
        }

        return sha1(json_encode($parts, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function findExistingNotification(User $user, string $type, array $data): ?LmsNotification
    {
        $dedupeKey = $data['dedupe_key'] ?? null;

        if (blank($dedupeKey)) {
            return null;
        }

        return LmsNotification::query()
            ->where('user_id', $user->id)
            ->where('type', $type)
            ->where('data->dedupe_key', $dedupeKey)
            ->first();
    }
}
