@extends('layouts.app')

@section('title', 'Detail Materi')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $timezone = config('app.timezone', 'Asia/Jakarta');

    if ($material->kelas && Route::has('assistant.courses.show')) {
        $backUrl = route('assistant.courses.show', $material->kelas);
    } elseif (Route::has('assistant.materi.index')) {
        $backUrl = route('assistant.materi.index');
    } elseif (Route::has('assistant.dashboard')) {
        $backUrl = route('assistant.dashboard');
    } else {
        $backUrl = '#';
    }

    $course = $material->kelas?->course;
    $class = $material->kelas;

    $publishedAt = $material->published_at
        ? $material->published_at->timezone($timezone)->format('d M Y H:i') . ' WIB'
        : '-';

    $createdAt = $material->created_at
        ? $material->created_at->timezone($timezone)->format('d M Y H:i') . ' WIB'
        : '-';

    $isLink = $material->file_path && str_starts_with($material->file_path, 'http');

    $fileUrl = null;

    if ($material->file_path) {
        $fileUrl = $isLink
            ? $material->file_path
            : asset('storage/'.$material->file_path);
    }
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Asisten Praktikum</div>

    <h1>Detail Materi</h1>

    <p>
        Lihat informasi materi praktikum, kelas tujuan, tipe materi, deskripsi, dan file atau link yang sudah dibuat.
    </p>

    <div class="hero-actions">
        <a href="{{ $backUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('assistant.materi.edit'))
            <a href="{{ route('assistant.materi.edit', $material) }}" class="btn btn-primary">
                Edit Materi
            </a>
        @endif

        @if($fileUrl)
            <a href="{{ $fileUrl }}" target="_blank" class="btn">
                {{ $isLink ? 'Buka Link' : 'Download File' }}
            </a>
        @endif
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">
                {{ $material->title }}
            </h2>

            <div class="section-subtitle">
                Informasi utama materi praktikum.
            </div>
        </div>

        <span class="status-pill status-info">
            {{ strtoupper($material->type ?? 'Materi') }}
        </span>
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
            <div class="stat-label">Dipublikasikan</div>

            <div class="stat-value" style="font-size: 20px;">
                {{ $publishedAt }}
            </div>

            <div class="stat-note">
                Waktu materi dapat terlihat oleh mahasiswa.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Dibuat</div>

            <div class="stat-value" style="font-size: 20px;">
                {{ $createdAt }}
            </div>

            <div class="stat-note">
                Waktu materi dibuat di sistem.
            </div>
        </div>
    </div>
</section>

<div class="grid grid-3">
    <section class="card" style="grid-column: span 2;">
        <div class="section-header">
            <div>
                <h2 class="section-title">Deskripsi Materi</h2>
                <div class="section-subtitle">
                    Ringkasan atau catatan materi yang akan dibaca mahasiswa.
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
        >{{ $material->description ?: 'Materi ini belum memiliki deskripsi.' }}</div>
    </section>

    <aside class="card">
        <div class="section-header">
            <div>
                <h2 class="section-title">File / Link Materi</h2>
                <div class="section-subtitle">
                    Akses file atau link yang terhubung dengan materi ini.
                </div>
            </div>
        </div>

        @if($fileUrl)
            <div class="list-stack">
                <div class="list-item">
                    <div>
                        <h3 class="item-title">
                            {{ $isLink ? 'Link Materi' : 'File Materi' }}
                        </h3>

                        <div class="item-meta">
                            {{ $isLink ? 'Materi ini berupa link eksternal.' : 'Materi ini berupa file yang tersimpan di storage.' }}
                        </div>
                    </div>

                    <span class="status-pill {{ $isLink ? 'status-info' : 'status-success' }}">
                        {{ $isLink ? 'Link' : 'File' }}
                    </span>
                </div>

                <a href="{{ $fileUrl }}" target="_blank" class="btn btn-primary" style="width: 100%;">
                    {{ $isLink ? 'Buka Link' : 'Download File' }}
                </a>
            </div>
        @else
            <div class="empty-state">
                <div style="font-size: 30px; margin-bottom: 8px;">📘</div>

                <h3 class="empty-state-title">
                    Belum ada file/link
                </h3>

                <p class="empty-state-text">
                    Materi ini belum memiliki file atau link.
                </p>
            </div>
        @endif
    </aside>
</div>

<section class="card" style="margin-top: 22px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Aksi Materi</h2>
            <div class="section-subtitle">
                Kembali ke halaman sebelumnya atau ubah data materi.
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a class="btn" href="{{ $backUrl }}">
            Kembali
        </a>

        @if(Route::has('assistant.materi.edit'))
            <a class="btn btn-primary" href="{{ route('assistant.materi.edit', $material) }}">
                Edit Materi
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