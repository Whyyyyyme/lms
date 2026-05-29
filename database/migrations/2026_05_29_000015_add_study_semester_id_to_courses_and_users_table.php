<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('courses') && ! Schema::hasColumn('courses', 'study_semester_id')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->foreignId('study_semester_id')
                    ->nullable()
                    ->after('academic_year_id')
                    ->constrained('study_semesters')
                    ->nullOnDelete();
            });

            $defaultSemesterId = DB::table('study_semesters')->where('level', 1)->value('id');

            if ($defaultSemesterId) {
                DB::table('courses')
                    ->whereNull('study_semester_id')
                    ->update(['study_semester_id' => $defaultSemesterId]);
            }
        }

        if (Schema::hasTable('users') && ! Schema::hasColumn('users', 'study_semester_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('study_semester_id')
                    ->nullable()
                    ->after('kelas_id')
                    ->constrained('study_semesters')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'study_semester_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('study_semester_id');
            });
        }

        if (Schema::hasTable('courses') && Schema::hasColumn('courses', 'study_semester_id')) {
            Schema::table('courses', function (Blueprint $table) {
                $table->dropConstrainedForeignId('study_semester_id');
            });
        }
    }
};
