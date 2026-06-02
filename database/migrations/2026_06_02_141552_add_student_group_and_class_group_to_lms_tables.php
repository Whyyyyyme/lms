<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'student_group')) {
                $table->string('student_group', 20)
                    ->nullable()
                    ->after('study_semester_id');
            }
        });

        Schema::table('classes', function (Blueprint $table) {
            if (! Schema::hasColumn('classes', 'class_type')) {
                $table->string('class_type', 20)
                    ->default('regular')
                    ->after('course_id');
            }

            if (! Schema::hasColumn('classes', 'student_group')) {
                $table->string('student_group', 20)
                    ->nullable()
                    ->after('class_type');
            }

            if (! Schema::hasColumn('classes', 'group_label')) {
                $table->string('group_label', 50)
                    ->nullable()
                    ->after('student_group');
            }

            if (! Schema::hasColumn('classes', 'group_members')) {
                $table->json('group_members')
                    ->nullable()
                    ->after('group_label');
            }
        });
    }

    public function down(): void
    {
        Schema::table('classes', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('classes', 'group_members')) {
                $columns[] = 'group_members';
            }

            if (Schema::hasColumn('classes', 'group_label')) {
                $columns[] = 'group_label';
            }

            if (Schema::hasColumn('classes', 'student_group')) {
                $columns[] = 'student_group';
            }

            if (Schema::hasColumn('classes', 'class_type')) {
                $columns[] = 'class_type';
            }

            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'student_group')) {
                $table->dropColumn('student_group');
            }
        });
    }
};