@extends('layouts.app')

@section('title', 'Dashboard Asisten')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $safe = fn (string $name) => Route::has($name) ? route($name) : '#';
    $statistics = $statistics ?? [];
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Asisten Praktikum</div>

    <h1>Dashboard Pengelolaan Praktikum</h1>

    <p>
        Kelola aktivitas praktikum mulai dari materi, tugas, absensi, submission,
        pengumuman, hingga export rekap nilai dan absensi mahasiswa.
    </p>

    <div class="hero-actions">
        <a href="{{ $safe('assistant.materi.create') }}" class="btn btn-primary">
            📘 Upload Materi
        </a>

        <a href="{{ $safe('assistant.tugas.create') }}" class="btn">
            📝 Buat Tugas
        </a>

        <a href="{{ $safe('assistant.attendances.create') }}" class="btn">
            ✅ Buat Absensi
        </a>
    </div>
</section>

<div class="grid grid-4" style="margin-bottom: 22px;">
    <div class="stat-card">
        <div class="stat-label">Kelas Diampu</div>
        <div class="stat-value">{{ $statistics['total_kelas'] ?? 0 }}</div>
        <div class="stat-note">Total kelas praktikum yang kamu kelola.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Mahasiswa</div>
        <div class="stat-value">{{ $statistics['total_mahasiswa'] ?? 0 }}</div>
        <div class="stat-note">Mahasiswa yang terdaftar pada kelas kamu.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Materi</div>
        <div class="stat-value">{{ $statistics['total_materi'] ?? 0 }}</div>
        <div class="stat-note">Materi praktikum yang sudah dibuat.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Belum Dinilai</div>
        <div class="stat-value">{{ $statistics['total_submission_belum_dinilai'] ?? 0 }}</div>
        <div class="stat-note">Submission mahasiswa yang perlu diperiksa.</div>
    </div>
</div>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Aksi Cepat Asisten</h2>
            <div class="section-subtitle">
                Gunakan menu berikut untuk mengelola aktivitas praktikum sesuai fitur yang tersedia.
            </div>
        </div>
    </div>

    <div class="grid grid-3">
        <a href="{{ $safe('assistant.materi.create') }}" class="action-card card">
            <div class="metric-pill">📘 Materi</div>

            <h3 class="course-title">Upload Materi</h3>

            <p class="course-meta">
                Tambahkan materi praktikum berupa PDF, dokumen, atau link pembelajaran.
            </p>

            <div class="course-footer">
                <span class="status-pill status-info">Kelola materi</span>
                <span style="font-weight: 900; color: var(--primary);">→</span>
            </div>
        </a>

        <a href="{{ $safe('assistant.tugas.create') }}" class="action-card card">
            <div class="metric-pill">📝 Tugas</div>

            <h3 class="course-title">Buat Tugas</h3>

            <p class="course-meta">
                Buat tugas praktikum, atur instruksi, dan tentukan deadline pengumpulan.
            </p>

            <div class="course-footer">
                <span class="status-pill status-info">Kelola tugas</span>
                <span style="font-weight: 900; color: var(--primary);">→</span>
            </div>
        </a>

        <a href="{{ $safe('assistant.attendances.create') }}" class="action-card card">
            <div class="metric-pill">✅ Absensi</div>

            <h3 class="course-title">Buat Absensi</h3>

            <p class="course-meta">
                Buka sesi absensi untuk mahasiswa sesuai jadwal praktikum.
            </p>

            <div class="course-footer">
                <span class="status-pill status-info">Kelola absensi</span>
                <span style="font-weight: 900; color: var(--primary);">→</span>
            </div>
        </a>

        <a href="{{ $safe('assistant.pengumuman.create') }}" class="action-card card">
            <div class="metric-pill">📢 Pengumuman</div>

            <h3 class="course-title">Buat Pengumuman</h3>

            <p class="course-meta">
                Kirim informasi atau pemberitahuan kepada mahasiswa pada kelas tertentu.
            </p>

            <div class="course-footer">
                <span class="status-pill status-info">Buat info</span>
                <span style="font-weight: 900; color: var(--primary);">→</span>
            </div>
        </a>

        <a href="{{ $safe('assistant.submissions.index') }}" class="action-card card">
            <div class="metric-pill">📥 Submission</div>

            <h3 class="course-title">Cek Submission</h3>

            <p class="course-meta">
                Lihat pengumpulan tugas mahasiswa, beri nilai, dan input feedback.
            </p>

            <div class="course-footer">
                <span class="status-pill status-info">Periksa tugas</span>
                <span style="font-weight: 900; color: var(--primary);">→</span>
            </div>
        </a>

        <a href="{{ $safe('assistant.exports.scores.excel') }}" class="action-card card">
            <div class="metric-pill">📗 Export</div>

            <h3 class="course-title">Export Nilai</h3>

            <p class="course-meta">
                Download rekap nilai mahasiswa dalam format Excel.
            </p>

            <div class="course-footer">
                <span class="status-pill status-info">Download</span>
                <span style="font-weight: 900; color: var(--primary);">→</span>
            </div>
        </a>
    </div>
</section>
@endsection