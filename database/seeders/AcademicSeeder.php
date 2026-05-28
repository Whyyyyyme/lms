<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AcademicSeeder extends Seeder
{
    public function run(): void
    {
        $academicYearId = DB::table('academic_years')->updateOrInsert(
            ['year' => '2025/2026', 'semester' => 'genap'],
            ['is_active' => true, 'created_at' => now(), 'updated_at' => now()]
        );

        $academicYear = DB::table('academic_years')
            ->where('year', '2025/2026')
            ->where('semester', 'genap')
            ->first();

        $courses = [
            [
                'name' => 'Cloud Computing Praktikum',
                'code' => 'CCP601',
                'sks' => 2,
            ],
            [
                'name' => 'Text Mining Praktikum',
                'code' => 'TMP602',
                'sks' => 2,
            ],
        ];

        foreach ($courses as $course) {
            DB::table('courses')->updateOrInsert(
                ['code' => $course['code']],
                [
                    'academic_year_id' => $academicYear->id,
                    'name' => $course['name'],
                    'sks' => $course['sks'],
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }

        $asisten1 = User::where('email', 'asisten1@lms.test')->first();
        $asisten2 = User::where('email', 'asisten2@lms.test')->first();
        $cloud = DB::table('courses')->where('code', 'CCP601')->first();
        $textMining = DB::table('courses')->where('code', 'TMP602')->first();

        DB::table('classes')->updateOrInsert(
            ['course_id' => $cloud->id, 'name' => 'Kelas A'],
            [
                'assistant_id' => $asisten1?->id,
                'room' => 'Lab Komputer 1',
                'schedule' => 'Senin, 10:00-12:00',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('classes')->updateOrInsert(
            ['course_id' => $textMining->id, 'name' => 'Kelas B'],
            [
                'assistant_id' => $asisten2?->id,
                'room' => 'Lab Komputer 2',
                'schedule' => 'Rabu, 13:00-15:00',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $classA = DB::table('classes')->where('course_id', $cloud->id)->where('name', 'Kelas A')->first();
        $classB = DB::table('classes')->where('course_id', $textMining->id)->where('name', 'Kelas B')->first();

        $students = User::where('role', 'mahasiswa')->orderBy('id')->get();

        foreach ($students as $index => $student) {
            $class = $index < 5 ? $classA : $classB;

            DB::table('class_students')->updateOrInsert(
                ['class_id' => $class->id, 'student_id' => $student->id],
                ['created_at' => now(), 'updated_at' => now()]
            );

            $student->update(['kelas_id' => $class->id]);
        }
    }
}
