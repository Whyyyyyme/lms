@extends('layouts.app')

@section('title', 'Tambah Kelas Praktikum')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $courses = $courses ?? collect();

    $cancelUrl = Route::has('admin.kelas.index')
        ? route('admin.kelas.index')
        : (Route::has('admin.dashboard') ? route('admin.dashboard') : '#');
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Tambah Kelas Praktikum</h1>

    <p>
        Buat kelas praktikum untuk mata kuliah tertentu, atur tipe kelas, rombel,
        jadwal, ruangan, dan hubungkan dengan asisten praktikum.
    </p>

    <div class="hero-actions">
        <a href="{{ $cancelUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('admin.kelas.index'))
            <a href="{{ route('admin.kelas.index') }}" class="btn btn-primary">
                🏫 Semua Kelas
            </a>
        @endif
    </div>
</section>

@if($courses->isEmpty())
    <div class="alert alert-error" style="margin-bottom: 18px;">
        Buat mata kuliah terlebih dahulu sebelum membuat kelas praktikum.
    </div>
@endif

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Form Kelas Praktikum</h2>
            <div class="section-subtitle">
                Isi data kelas, pilih mata kuliah, asisten, tipe kelas, rombel, ruang, jadwal, dan status kelas.
            </div>
        </div>
    </div>

    <form action="{{ route('admin.kelas.store') }}" method="POST" class="form-card">
        @csrf

        @include('admin.classes._form')

        <div class="form-actions">
            <a href="{{ $cancelUrl }}" class="btn">
                Batal
            </a>

            <button
                type="submit"
                class="btn btn-primary"
                @disabled($courses->isEmpty())
            >
                Simpan Kelas
            </button>
        </div>
    </form>
</section>
@endsection