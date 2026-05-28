<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $classA = DB::table('classes')->where('name', 'Kelas A')->first();
        $classB = DB::table('classes')->where('name', 'Kelas B')->first();
        $asisten1 = User::where('email', 'asisten1@lms.test')->first();
        $asisten2 = User::where('email', 'asisten2@lms.test')->first();

        if (! $classA || ! $classB || ! $asisten1 || ! $asisten2) {
            return;
        }

        DB::table('materials')->updateOrInsert(
            ['class_id' => $classA->id, 'title' => 'Pengenalan Cloud Computing'],
            [
                'description' => 'Materi pengantar komputasi awan, model layanan IaaS, PaaS, dan SaaS.',
                'file_path' => 'materials/pengenalan-cloud-computing.pdf',
                'type' => 'pdf',
                'created_by' => $asisten1->id,
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('materials')->updateOrInsert(
            ['class_id' => $classB->id, 'title' => 'Pengenalan Text Mining'],
            [
                'description' => 'Materi awal text mining, preprocessing teks, TF-IDF, LSA, dan LDA.',
                'file_path' => 'materials/pengenalan-text-mining.pdf',
                'type' => 'pdf',
                'created_by' => $asisten2->id,
                'published_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('assignments')->updateOrInsert(
            ['class_id' => $classA->id, 'title' => 'Tugas 1 - Deploy Laravel ke Cloud'],
            [
                'description' => 'Upload laporan PDF berisi langkah deploy Laravel ke layanan cloud.',
                'file_path' => null,
                'deadline' => now()->addDays(7),
                'max_score' => 100,
                'created_by' => $asisten1->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('assignments')->updateOrInsert(
            ['class_id' => $classB->id, 'title' => 'Tugas 1 - Preprocessing Teks'],
            [
                'description' => 'Buat ringkasan proses tokenizing, filtering, stemming, dan TF-IDF.',
                'file_path' => null,
                'deadline' => now()->addDays(10),
                'max_score' => 100,
                'created_by' => $asisten2->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('announcements')->updateOrInsert(
            ['class_id' => $classA->id, 'title' => 'Praktikum Cloud Computing Dimulai'],
            [
                'content' => 'Silakan membaca materi pengantar sebelum praktikum pertama dimulai.',
                'created_by' => $asisten1->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('announcements')->updateOrInsert(
            ['class_id' => $classB->id, 'title' => 'Persiapan Praktikum Text Mining'],
            [
                'content' => 'Pastikan Python dan library dasar text mining sudah terpasang.',
                'created_by' => $asisten2->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
