@extends('layouts.app')

@section('title', 'Detail Tugas')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $timezone = config('app.timezone', 'Asia/Jakarta');

    if ($assignment->kelas && Route::has('assistant.courses.show')) {
        $backUrl = route('assistant.courses.show', $assignment->kelas);
    } elseif (Route::has('assistant.tugas.index')) {
        $backUrl = route('assistant.tugas.index');
    } elseif (Route::has('assistant.dashboard')) {
        $backUrl = route('assistant.dashboard');
    } else {
        $backUrl = '#';
    }

    $course = $assignment->kelas?->course;
    $class = $assignment->kelas;

    $deadline = $assignment->deadline
        ? $assignment->deadline->timezone($timezone)->format('d M Y H:i') . ' WIB'
        : '-';

    $publishedAt = $assignment->published_at
        ? $assignment->published_at->timezone($timezone)->format('d M Y H:i') . ' WIB'
        : 'Langsung tampil';

    $createdAt = $assignment->created_at
        ? $assignment->created_at->timezone($timezone)->format('d M Y H:i') . ' WIB'
        : '-';

    $isPastDeadline = $assignment->deadline && $assignment->deadline->lessThan(now());

    $fileUrl = $assignment->file_path
        ? asset('storage/'.$assignment->file_path)
        : null;
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Asisten Praktikum</div>

    <h1>Detail Tugas</h1>

    <p>
        Lihat detail tugas praktikum, kelas tujuan, deadline, nilai maksimal, deskripsi, dan lampiran tugas.
    </p>

    <div class="hero-actions">
        <a href="{{ $backUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('assistant.tugas.edit'))
            <a href="{{ route('assistant.tugas.edit', $assignment) }}" class="btn btn-primary">
                Edit Tugas
            </a>
        @endif

        @if($fileUrl)
            <a href="{{ $fileUrl }}" target="_blank" class="btn">
                Download Lampiran
            </a>
        @endif
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">
                {{ $assignment->title }}
            </h2>

            <div class="section-subtitle">
                Informasi utama tugas praktikum.
            </div>
        </div>

        @if($isPastDeadline)
            <span class="status-pill status-danger">
                Deadline Lewat
            </span>
        @else
            <span class="status-pill status-warning">
                Aktif
            </span>
        @endif
    </div>

    <div class="grid grid-4">
        <div class="stat-card">
            <div class="stat-label">Mata Kuliah</div>

            <div class="stat-value" style="font-size: 20px;">
                {{ $course?->name ?? 'Mata kuliah tidak ditemukan' }}
            </div>

            <div class="stat-note">
                {{ $course?->code ? 'Kode: '.$course->code : 'Kode mata kuliah belum tersedia.' }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Kelas</div>

            <div class="stat-value" style="font-size: 20px;">
                {{ $class?->name ?? 'Kelas tidak ditemukan' }}
            </div>

            <div class="stat-note">
                {{ $course?->studySemester?->name ? 'Semester '.$course->studySemester->name : 'Semester belum tersedia.' }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Deadline</div>

            <div class="stat-value" style="font-size: 20px;">
                {{ $deadline }}
            </div>

            <div class="stat-note">
                Batas akhir mahasiswa mengumpulkan tugas.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Nilai Maksimal</div>

            <div class="stat-value">
                {{ $assignment->max_score ?? 0 }}
            </div>

            <div class="stat-note">
                Skor maksimal untuk tugas ini.
            </div>
        </div>
    </div>
</section>

<div class="grid grid-3">
    <section class="card" style="grid-column: span 2;">
        <div class="section-header">
            <div>
                <h2 class="section-title">Deskripsi Tugas</h2>
                <div class="section-subtitle">
                    Instruksi tugas yang akan dibaca mahasiswa.
                </div>
            </div>
        </div>

        <div
            style="
                padding: 18px;
                border: 1px solid var(--line);
                border-radius: 18px;
                background: #f8fafc;
                color: #334155;
                line-height: 1.75;
                white-space: pre-line;
            "
        >{{ $assignment->description ?: 'Tugas ini belum memiliki deskripsi.' }}</div>
    </section>

    <aside class="card">
        <div class="section-header">
            <div>
                <h2 class="section-title">Lampiran Tugas</h2>
                <div class="section-subtitle">
                    File instruksi tambahan untuk mahasiswa.
                </div>
            </div>
        </div>

        @if($fileUrl)
            <div class="list-stack">
                <div class="list-item">
                    <div>
                        <h3 class="item-title">
                            File Lampiran
                        </h3>

                        <div class="item-meta">
                            Lampiran tugas tersimpan di storage dan dapat diunduh.
                        </div>
                    </div>

                    <span class="status-pill status-success">
                        File
                    </span>
                </div>

                <a href="{{ $fileUrl }}" target="_blank" class="btn btn-primary" style="width: 100%;">
                    Download Lampiran
                </a>
            </div>
        @else
            <div class="empty-state">
                <div style="font-size: 30px; margin-bottom: 8px;">📝</div>

                <h3 class="empty-state-title">
                    Belum ada lampiran
                </h3>

                <p class="empty-state-text">
                    Tugas ini belum memiliki file lampiran.
                </p>
            </div>
        @endif
    </aside>
</div>

<section class="card" style="margin-top: 22px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Informasi Tambahan</h2>
            <div class="section-subtitle">
                Status publikasi dan waktu pembuatan tugas.
            </div>
        </div>
    </div>

    <div class="grid grid-3">
        <div class="stat-card">
            <div class="stat-label">Waktu Publikasi</div>

            <div class="stat-value" style="font-size: 20px;">
                {{ $publishedAt }}
            </div>

            <div class="stat-note">
                Waktu tugas mulai tampil ke mahasiswa.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Dibuat</div>

            <div class="stat-value" style="font-size: 20px;">
                {{ $createdAt }}
            </div>

            <div class="stat-note">
                Waktu tugas dibuat di sistem.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Status Deadline</div>

            <div class="stat-value" style="font-size: 20px;">
                {{ $isPastDeadline ? 'Lewat' : 'Aktif' }}
            </div>

            <div class="stat-note">
                Status berdasarkan waktu deadline tugas.
            </div>
        </div>
    </div>
</section>

<section class="card" style="margin-top: 22px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Aksi Tugas</h2>
            <div class="section-subtitle">
                Kembali ke halaman sebelumnya atau ubah data tugas.
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a class="btn" href="{{ $backUrl }}">
            Kembali
        </a>

        @if(Route::has('assistant.tugas.edit'))
            <a class="btn btn-primary" href="{{ route('assistant.tugas.edit', $assignment) }}">
                Edit Tugas
            </a>
        @endif
    </div>
</section>

<style>
    @media (max-width: 1100px) {
        .grid.grid-3 > section[style*="grid-column"] {
            grid-column: span 1 !important;
        }
    }
</style>
@endsection