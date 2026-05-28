<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('assistant_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name'); // contoh: Kelas A
            $table->string('room')->nullable();
            $table->string('schedule')->nullable(); // contoh: Senin, 10:00-12:00
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['course_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classes');
    }
};
