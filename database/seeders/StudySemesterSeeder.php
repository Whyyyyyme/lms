<?php

namespace Database\Seeders;

use App\Models\StudySemester;
use Illuminate\Database\Seeder;

class StudySemesterSeeder extends Seeder
{
    public function run(): void
    {
        for ($level = 1; $level <= 8; $level++) {
            StudySemester::updateOrCreate(
                ['level' => $level],
                [
                    'name' => 'Semester ' . $level,
                    'description' => 'Semester mahasiswa tingkat ' . $level,
                    'is_active' => true,
                ]
            );
        }
    }
}
