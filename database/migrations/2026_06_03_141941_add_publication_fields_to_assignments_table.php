<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('assignments', 'published_at')) {
                $table->timestamp('published_at')->nullable()->after('deadline');
            }

            if (! Schema::hasColumn('assignments', 'published_notification_sent_at')) {
                $table->timestamp('published_notification_sent_at')->nullable()->after('published_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assignments', function (Blueprint $table) {
            if (Schema::hasColumn('assignments', 'published_notification_sent_at')) {
                $table->dropColumn('published_notification_sent_at');
            }

            if (Schema::hasColumn('assignments', 'published_at')) {
                $table->dropColumn('published_at');
            }
        });
    }
};