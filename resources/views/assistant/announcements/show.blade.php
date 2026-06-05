@extends('layouts.app')

@section('title', 'Detail Pengumuman')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $timezone = config('app.timezone', 'Asia/Jakarta');

    $backUrl = Route::has('assistant.pengumuman.index')
        ? route('assistant.pengumuman.index')
        : (Route::has('assistant.dashboard') ? route('assistant.dashboard') : '#');

    $createdAt = $announcement->created_at
        ? $announcement->created_at->timezone($timezone)->format('d M Y H:i') . ' WIB'
        : '-';

    $updatedAt = $announcement->updated_at
        ? $announcement->updated_at->timezone($timezone)->format('d M Y H:i') . ' WIB'
        : '-';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Asisten Praktikum</div>

    <h1>Detail Pengumuman</h1>

    <p>
        Lihat detail pengumuman yang sudah dibuat untuk mahasiswa kelas praktikum.
        Kamu juga bisa mengedit pengumuman jika ada informasi yang perlu diperbarui.
    </p>

    <div class="hero-actions">
        <a href="{{ $backUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('assistant.pengumuman.edit'))
            <a href="{{ route('assistant.pengumuman.edit', $announcement) }}" class="btn btn-primary">
                Edit Pengumuman
            </a>
        @endif
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">
                {{ $announcement->title }}
            </h2>

            <div class="section-subtitle">
                Informasi kelas tujuan dan waktu pembuatan pengumuman.
            </div>
        </div>

        <span class="status-pill status-info">
            Pengumuman
        </span>
    </div>

    <div class="grid grid-4">
        <div class="stat-card">
            <div class="stat-label">Mata Kuliah</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $announcement->kelas?->course?->name ?? 'Mata kuliah tidak ditemukan' }}
            </div>

            <div class="stat-note">
                {{ $announcement->kelas?->course?->code ? 'Kode: '.$announcement->kelas->course->code : 'Kode mata kuliah belum tersedia.' }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Kelas</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $announcement->kelas?->name ?? 'Kelas tidak ditemukan' }}
            </div>

            <div class="stat-note">
                {{ $announcement->kelas?->course?->studySemester?->name ? 'Semester '.$announcement->kelas->course->studySemester->name : 'Semester belum tersedia.' }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Dibuat</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $createdAt }}
            </div>

            <div class="stat-note">
                Waktu pengumuman dibuat.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Diperbarui</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $updatedAt }}
            </div>

            <div class="stat-note">
                Waktu terakhir pengumuman diperbarui.
            </div>
        </div>
    </div>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Isi Pengumuman</h2>
            <div class="section-subtitle">
                Konten pengumuman yang dapat dibaca mahasiswa.
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
    >{{ $announcement->content }}</div>

    <div class="form-actions">
        <a class="btn" href="{{ $backUrl }}">
            Kembali
        </a>

        @if(Route::has('assistant.pengumuman.edit'))
            <a class="btn btn-primary" href="{{ route('assistant.pengumuman.edit', $announcement) }}">
                Edit Pengumuman
            </a>
        @endif
    </div>
</section>
@endsection