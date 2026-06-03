<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'class_id',
        'title',
        'description',
        'file_path',
        'deadline',
        'max_score',
        'created_by',
        'published_at',
        'published_notification_sent_at',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'published_at' => 'datetime',
        'published_notification_sent_at' => 'datetime',
        'max_score' => 'integer',
    ];

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(PraktikumClass::class, 'class_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Tugas yang deadline-nya masih aktif.
     */
    public function scopeActive($query)
    {
        return $query->where('deadline', '>=', now());
    }

    /**
     * Tugas yang deadline-nya sudah lewat.
     */
    public function scopeExpired($query)
    {
        return $query->where('deadline', '<', now());
    }

    /**
     * Tugas yang deadline-nya mendekati batas waktu.
     */
    public function scopeDeadlineSoon($query, int $days = 3)
    {
        return $query->whereBetween('deadline', [
            now(),
            now()->addDays($days),
        ]);
    }

    /**
     * Tugas yang sudah boleh tampil di halaman mahasiswa.
     *
     * Jika published_at kosong, tugas dianggap langsung dipublikasikan.
     * Jika published_at berisi waktu masa depan, tugas belum tampil.
     */
    public function scopePublished($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('published_at')
                ->orWhere('published_at', '<=', now());
        });
    }

    /**
     * Tugas yang dijadwalkan tampil di masa depan.
     */
    public function scopeScheduled($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '>', now());
    }

    /**
     * Cek apakah tugas masih terjadwal dan belum tampil ke mahasiswa.
     */
    public function getIsScheduledAttribute(): bool
    {
        return $this->published_at !== null && $this->published_at->isFuture();
    }

    /**
     * Cek apakah tugas sudah tampil ke mahasiswa.
     */
    public function getIsPublishedAttribute(): bool
    {
        return $this->published_at === null || $this->published_at->lte(now());
    }

    /**
     * Label status publikasi untuk tampilan asisten.
     */
    public function getPublicationStatusAttribute(): string
    {
        if ($this->is_scheduled) {
            return 'Terjadwal';
        }

        return 'Dipublikasikan';
    }
}