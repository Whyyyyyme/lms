@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('content')
@php
    use Illuminate\Support\Facades\Route;
    $safe = fn($name) => Route::has($name) ? route($name) : '#';
    $statistics = $statistics ?? [];
@endphp
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Dashboard Admin', 'description' => 'Kelola data utama LMS Praktikum dari satu halaman.'])
<div class="grid grid-4" style="margin-bottom:18px;">
    @include('partials.stat-card', ['label' => 'User', 'value' => $statistics['total_users'] ?? 0, 'icon' => '👥'])
    @include('partials.stat-card', ['label' => 'Matakuliah', 'value' => $statistics['total_courses'] ?? 0, 'icon' => '📚'])
    @include('partials.stat-card', ['label' => 'Kelas', 'value' => $statistics['total_classes'] ?? 0, 'icon' => '🏫'])
    @include('partials.stat-card', ['label' => 'Submission', 'value' => $statistics['total_submissions'] ?? 0, 'icon' => '📥'])
</div>
<div class="grid grid-3">
    @include('partials.action-card', ['title' => 'Tambah User', 'description' => 'Tambah admin, asisten, atau mahasiswa.', 'href' => $safe('admin.users.create'), 'icon' => '👥'])
    @include('partials.action-card', ['title' => 'Tambah Tahun Akademik', 'description' => 'Atur tahun akademik dan semester aktif.', 'href' => $safe('admin.tahun-akademik.create'), 'icon' => '📅'])
    @include('partials.action-card', ['title' => 'Tambah Matakuliah', 'description' => 'Buat matakuliah praktikum baru.', 'href' => $safe('admin.matakuliah.create'), 'icon' => '📚'])
    @include('partials.action-card', ['title' => 'Tambah Kelas', 'description' => 'Buat kelas, jadwal, asisten, dan mahasiswa.', 'href' => $safe('admin.kelas.create'), 'icon' => '🏫'])
    @include('partials.action-card', ['title' => 'Laporan Nilai', 'description' => 'Lihat rekap nilai submission mahasiswa.', 'href' => $safe('admin.reports.scores'), 'icon' => '🧾'])
    @include('partials.action-card', ['title' => 'Pengaturan', 'description' => 'Atur identitas sistem dan kampus.', 'href' => $safe('admin.settings.edit'), 'icon' => '⚙️'])
</div>
@endsection
