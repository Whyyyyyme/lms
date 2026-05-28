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
    ];

    protected function casts(): array
    {
        return [
            'deadline' => 'datetime',
            'max_score' => 'integer',
        ];
    }

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

    public function scopeActive($query)
    {
        return $query->where('deadline', '>=', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('deadline', '<', now());
    }

    public function scopeDeadlineSoon($query, int $days = 3)
    {
        return $query->whereBetween('deadline', [now(), now()->addDays($days)]);
    }
}
