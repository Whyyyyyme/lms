<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class PraktikumClass extends Model
{
    use HasFactory;

    protected $table = 'classes';

    protected $fillable = [
        'course_id',
        'assistant_id',
        'name',
        'room',
        'schedule',
        'is_active',
        'class_type',
        'student_group',
        'group_label',
        'group_members',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'group_members' => 'array',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function assistant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assistant_id');
    }

    public function students(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'class_students',
            'class_id',
            'student_id'
        )->withTimestamps();
    }

    public function usersWithMainClass(): HasMany
    {
        return $this->hasMany(User::class, 'kelas_id');
    }

    public function materials(): HasMany
    {
        return $this->hasMany(Material::class, 'class_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class, 'class_id');
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class, 'class_id');
    }

    public function activeAttendance(): HasOne
    {
        return $this->hasOne(Attendance::class, 'class_id')->where('is_open', true)->latestOfMany();
    }

    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class, 'class_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
