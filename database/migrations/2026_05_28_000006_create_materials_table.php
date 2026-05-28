<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('type', ['pdf', 'video', 'dokumen', 'link'])->default('dokumen');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
