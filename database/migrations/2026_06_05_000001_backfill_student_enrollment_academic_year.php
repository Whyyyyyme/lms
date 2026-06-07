<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('academic_years') || ! Schema::hasTable('student_semester_enrollments')) {
            return;
        }

        $activeAcademicYearId = DB::table('academic_years')
            ->where('is_active', true)
            ->orderByDesc('id')
            ->value('id');

        if (! $activeAcademicYearId) {
            return;
        }

        DB::table('student_semester_enrollments')
            ->whereNull('academic_year_id')
            ->orderBy('id')
            ->get()
            ->each(function ($enrollment) use ($activeAcademicYearId): void {
                $existing = DB::table('student_semester_enrollments')
                    ->where('student_id', $enrollment->student_id)
                    ->where('study_semester_id', $enrollment->study_semester_id)
                    ->where('academic_year_id', $activeAcademicYearId)
                    ->first();

                if ($existing) {
                    if ((bool) $enrollment->is_active) {
                        DB::table('student_semester_enrollments')
                            ->where('id', $existing->id)
                            ->update([
                                'is_active' => true,
                                'enrolled_at' => $existing->enrolled_at ?: ($enrollment->enrolled_at ?: now()),
                                'updated_at' => now(),
                            ]);
                    }

                    DB::table('student_semester_enrollments')
                        ->where('id', $enrollment->id)
                        ->delete();

                    return;
                }

                DB::table('student_semester_enrollments')
                    ->where('id', $enrollment->id)
                    ->update([
                        'academic_year_id' => $activeAcademicYearId,
                        'updated_at' => now(),
                    ]);
            });
    }

    public function down(): void
    {
        // Sengaja tidak dikembalikan ke null agar riwayat akademik mahasiswa tidak hilang.
    }
};
