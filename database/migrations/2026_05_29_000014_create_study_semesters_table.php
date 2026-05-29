<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('study_semesters')) {
            Schema::create('study_semesters', function (Blueprint $table) {
                $table->id();
                $table->unsignedTinyInteger('level')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        $now = now();

        for ($level = 1; $level <= 8; $level++) {
            DB::table('study_semesters')->updateOrInsert(
                ['level' => $level],
                [
                    'name' => 'Semester ' . $level,
                    'description' => 'Semester mahasiswa tingkat ' . $level,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('study_semesters');
    }
};
