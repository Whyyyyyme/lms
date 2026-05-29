<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudySemester extends Model
{
    use HasFactory;

    protected $fillable = [
        'level',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'study_semester_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(StudentSemesterEnrollment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
