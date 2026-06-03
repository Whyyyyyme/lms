<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom extracted_text untuk menyimpan hasil baca file tugas dan materi.
     */
    public function up(): void
    {
        if (Schema::hasTable('assignments') && ! Schema::hasColumn('assignments', 'extracted_text')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->longText('extracted_text')->nullable()->after('file_path');
            });
        }

        if (Schema::hasTable('materials') && ! Schema::hasColumn('materials', 'extracted_text')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->longText('extracted_text')->nullable()->after('file_path');
            });
        }
    }

    /**
     * Hapus kolom extracted_text jika migration di-rollback.
     */
    public function down(): void
    {
        if (Schema::hasTable('assignments') && Schema::hasColumn('assignments', 'extracted_text')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->dropColumn('extracted_text');
            });
        }

        if (Schema::hasTable('materials') && Schema::hasColumn('materials', 'extracted_text')) {
            Schema::table('materials', function (Blueprint $table) {
                $table->dropColumn('extracted_text');
            });
        }
    }
};