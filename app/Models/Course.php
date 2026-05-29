<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'academic_year_id',
        'study_semester_id',
        'name',
        'code',
        'sks',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'sks' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }

    public function studySemester(): BelongsTo
    {
        return $this->belongsTo(StudySemester::class);
    }

    public function classes(): HasMany
    {
        return $this->hasMany(PraktikumClass::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
