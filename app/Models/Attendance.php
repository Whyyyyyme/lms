<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'session_date',
        'opened_by',
        'opened_at',
        'closed_at',
        'is_open',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'is_open' => 'boolean',
        ];
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(PraktikumClass::class, 'class_id');
    }

    public function opener(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function records(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class);
    }

    public function scopeOpen($query)
    {
        $now = now();

        return $query
            ->where('is_open', true)
            ->whereNotNull('opened_at')
            ->where('opened_at', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('closed_at')
                    ->orWhere('closed_at', '>', $now);
            });
    }

    public function scopeClosed($query)
    {
        $now = now();

        return $query
            ->where(function ($query) use ($now) {
                $query->where('is_open', false)
                    ->orWhere(function ($query) use ($now) {
                        $query->whereNotNull('closed_at')
                            ->where('closed_at', '<=', $now);
                    });
            });
    }

    public function hasStarted(): bool
    {
        return $this->opened_at !== null
            && $this->opened_at->lessThanOrEqualTo(now());
    }

    public function hasEnded(): bool
    {
        return $this->closed_at !== null
            && $this->closed_at->lessThanOrEqualTo(now());
    }

    public function isWaitingToOpen(): bool
    {
        return $this->opened_at !== null
            && $this->opened_at->greaterThan(now());
    }

    public function isWithinOpenWindow(): bool
    {
        if (! $this->opened_at || ! $this->closed_at) {
            return false;
        }

        $now = now();

        return $this->opened_at->lessThanOrEqualTo($now)
            && $this->closed_at->greaterThan($now);
    }

    public function syncOpenStatus(): bool
    {
        $shouldBeOpen = $this->isWithinOpenWindow();

        if ((bool) $this->is_open === $shouldBeOpen) {
            return false;
        }

        $this->forceFill([
            'is_open' => $shouldBeOpen,
        ])->save();

        $this->setAttribute('is_open', $shouldBeOpen);

        return true;
    }

    public function statusLabel(): string
    {
        if ($this->isWaitingToOpen()) {
            return 'Belum Dibuka';
        }

        if ($this->hasEnded()) {
            return 'Ditutup';
        }

        if ($this->isWithinOpenWindow()) {
            return 'Sedang Dibuka';
        }

        return $this->is_open ? 'Sedang Dibuka' : 'Tidak Aktif';
    }

    public function statusBadgeClass(): string
    {
        if ($this->isWithinOpenWindow()) {
            return 'badge-green';
        }

        if ($this->isWaitingToOpen()) {
            return 'badge-blue';
        }

        return 'badge-red';
    }
}