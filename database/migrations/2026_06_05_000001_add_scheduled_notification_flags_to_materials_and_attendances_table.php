<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            if (! Schema::hasColumn('materials', 'published_notification_sent_at')) {
                $table->timestamp('published_notification_sent_at')
                    ->nullable()
                    ->after('published_at')
                    ->index();
            }
        });

        Schema::table('attendances', function (Blueprint $table) {
            if (! Schema::hasColumn('attendances', 'opened_notification_sent_at')) {
                $table->timestamp('opened_notification_sent_at')
                    ->nullable()
                    ->after('is_open')
                    ->index();
            }

            if (! Schema::hasColumn('attendances', 'closed_notification_sent_at')) {
                $table->timestamp('closed_notification_sent_at')
                    ->nullable()
                    ->after('opened_notification_sent_at')
                    ->index();
            }
        });

        /**
         * Data lama yang sudah dipublikasikan/dibuka sebelum migration ini
         * dianggap sudah pernah diproses agar scheduler baru tidak mengirim
         * notifikasi lama secara massal.
         */
        if (Schema::hasColumn('materials', 'published_notification_sent_at')) {
            DB::table('materials')
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->whereNull('published_notification_sent_at')
                ->update([
                    'published_notification_sent_at' => now(),
                ]);
        }

        if (Schema::hasColumn('assignments', 'published_notification_sent_at')) {
            DB::table('assignments')
                ->where(function ($query) {
                    $query->whereNull('published_at')
                        ->orWhere('published_at', '<=', now());
                })
                ->whereNull('published_notification_sent_at')
                ->update([
                    'published_notification_sent_at' => now(),
                ]);
        }

        if (Schema::hasColumn('attendances', 'opened_notification_sent_at')) {
            DB::table('attendances')
                ->whereNotNull('opened_at')
                ->where('opened_at', '<=', now())
                ->whereNull('opened_notification_sent_at')
                ->update([
                    'opened_notification_sent_at' => now(),
                ]);
        }

        if (Schema::hasColumn('attendances', 'closed_notification_sent_at')) {
            DB::table('attendances')
                ->whereNotNull('closed_at')
                ->where('closed_at', '<=', now())
                ->whereNull('closed_notification_sent_at')
                ->update([
                    'closed_notification_sent_at' => now(),
                    'is_open' => false,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            if (Schema::hasColumn('attendances', 'closed_notification_sent_at')) {
                $table->dropColumn('closed_notification_sent_at');
            }

            if (Schema::hasColumn('attendances', 'opened_notification_sent_at')) {
                $table->dropColumn('opened_notification_sent_at');
            }
        });

        Schema::table('materials', function (Blueprint $table) {
            if (Schema::hasColumn('materials', 'published_notification_sent_at')) {
                $table->dropColumn('published_notification_sent_at');
            }
        });
    }
};
