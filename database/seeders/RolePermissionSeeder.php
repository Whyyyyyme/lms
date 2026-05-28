<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // Admin
            'kelola user',
            'kelola matakuliah',
            'kelola kelas',
            'kelola tahun akademik',
            'lihat semua laporan',
            'konfigurasi sistem',

            // Asisten
            'upload materi',
            'kelola tugas',
            'input nilai',
            'kelola absensi',
            'kirim pengumuman',
            'lihat submission',
            'export rekap',

            // Mahasiswa
            'lihat materi',
            'lihat tugas',
            'submit tugas',
            'lihat nilai',
            'absensi mandiri',
            'lihat jadwal',
            'akses chatbot',
            'lihat notifikasi',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $asisten = Role::firstOrCreate(['name' => 'asisten', 'guard_name' => 'web']);
        $mahasiswa = Role::firstOrCreate(['name' => 'mahasiswa', 'guard_name' => 'web']);

        $admin->syncPermissions($permissions);

        $asisten->syncPermissions([
            'upload materi',
            'kelola tugas',
            'input nilai',
            'kelola absensi',
            'kirim pengumuman',
            'lihat submission',
            'export rekap',
            'lihat materi',
            'lihat tugas',
            'lihat jadwal',
            'lihat notifikasi',
        ]);

        $mahasiswa->syncPermissions([
            'lihat materi',
            'lihat tugas',
            'submit tugas',
            'lihat nilai',
            'absensi mandiri',
            'lihat jadwal',
            'akses chatbot',
            'lihat notifikasi',
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
