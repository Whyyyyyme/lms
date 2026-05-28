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
        return $query->where('is_open', true);
    }

    public function scopeClosed($query)
    {
        return $query->where('is_open', false);
    }
}
