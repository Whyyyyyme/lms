<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('student_semester_enrollments')) {
            Schema::create('student_semester_enrollments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('study_semester_id')->constrained('study_semesters')->cascadeOnDelete();
                $table->foreignId('academic_year_id')->nullable()->constrained('academic_years')->nullOnDelete();
                $table->boolean('is_active')->default(true);
                $table->timestamp('enrolled_at')->nullable();
                $table->timestamps();

                $table->unique(['student_id', 'study_semester_id', 'academic_year_id'], 'student_semester_unique');
                $table->index(['study_semester_id', 'is_active']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('student_semester_enrollments');
    }
};
