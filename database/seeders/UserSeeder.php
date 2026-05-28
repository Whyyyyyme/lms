<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(
            ['email' => 'admin@lms.test'],
            [
                'name' => 'Admin LMS',
                'nim_nip' => 'ADM001',
                'password' => Hash::make('password'),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('admin');

        $assistants = [
            [
                'name' => 'Budi Asisten',
                'nim_nip' => 'AST001',
                'email' => 'asisten1@lms.test',
            ],
            [
                'name' => 'Sinta Asisten',
                'nim_nip' => 'AST002',
                'email' => 'asisten2@lms.test',
            ],
        ];

        foreach ($assistants as $assistantData) {
            $assistant = User::updateOrCreate(
                ['email' => $assistantData['email']],
                [
                    'name' => $assistantData['name'],
                    'nim_nip' => $assistantData['nim_nip'],
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $assistant->assignRole('asisten');
        }

        for ($i = 1; $i <= 10; $i++) {
            $student = User::updateOrCreate(
                ['email' => 'mahasiswa' . $i . '@lms.test'],
                [
                    'name' => 'Mahasiswa ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                    'nim_nip' => '231150' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                    'password' => Hash::make('password'),
                    'is_active' => true,
                    'email_verified_at' => now(),
                ]
            );

            $student->assignRole('mahasiswa');
        }
    }
}
