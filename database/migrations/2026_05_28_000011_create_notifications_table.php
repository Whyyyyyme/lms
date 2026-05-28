<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Kolom standar Laravel Notifications database channel.
            $table->string('type');
            $table->morphs('notifiable');

            // Kolom tambahan agar query navbar/notifikasi lebih sederhana.
            $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('message')->nullable();
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
