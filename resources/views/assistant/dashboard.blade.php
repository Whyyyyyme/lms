@extends('layouts.app')
@section('title', 'Dashboard Asisten')
@section('content')
@php
    use Illuminate\Support\Facades\Route;
    $safe = fn($name) => Route::has($name) ? route($name) : '#';
    $statistics = $statistics ?? [];
@endphp
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Dashboard Asisten Praktikum', 'description' => 'Kelola materi, tugas, absensi, submission, nilai, pengumuman, dan export.'])
<div class="grid grid-4" style="margin-bottom:18px;">
    @include('partials.stat-card', ['label' => 'Kelas Diampu', 'value' => $statistics['total_kelas'] ?? 0, 'icon' => '🏫'])
    @include('partials.stat-card', ['label' => 'Mahasiswa', 'value' => $statistics['total_mahasiswa'] ?? 0, 'icon' => '🎓'])
    @include('partials.stat-card', ['label' => 'Materi', 'value' => $statistics['total_materi'] ?? 0, 'icon' => '📘'])
    @include('partials.stat-card', ['label' => 'Belum Dinilai', 'value' => $statistics['total_submission_belum_dinilai'] ?? 0, 'icon' => '📥'])
</div>
<div class="grid grid-3">
    @include('partials.action-card', ['title' => 'Upload Materi', 'description' => 'Tambahkan PDF, dokumen, atau link video.', 'href' => $safe('assistant.materi.create'), 'icon' => '📘'])
    @include('partials.action-card', ['title' => 'Buat Tugas', 'description' => 'Buat tugas dan atur deadline.', 'href' => $safe('assistant.tugas.create'), 'icon' => '📝'])
    @include('partials.action-card', ['title' => 'Buat Absensi', 'description' => 'Buka sesi absensi untuk mahasiswa.', 'href' => $safe('assistant.attendances.create'), 'icon' => '✅'])
    @include('partials.action-card', ['title' => 'Buat Pengumuman', 'description' => 'Kirim informasi ke kelas tertentu.', 'href' => $safe('assistant.pengumuman.create'), 'icon' => '📢'])
    @include('partials.action-card', ['title' => 'Submission', 'description' => 'Lihat submission dan input nilai.', 'href' => $safe('assistant.submissions.index'), 'icon' => '📥'])
    @include('partials.action-card', ['title' => 'Export Nilai', 'description' => 'Download rekap nilai Excel.', 'href' => $safe('assistant.exports.scores.excel'), 'icon' => '📗'])
</div>
@endsection
