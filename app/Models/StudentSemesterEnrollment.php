<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentSemesterEnrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'study_semester_id',
        'academic_year_id',
        'is_active',
        'enrolled_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'enrolled_at' => 'datetime',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function studySemester(): BelongsTo
    {
        return $this->belongsTo(StudySemester::class);
    }

    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class);
    }
}
