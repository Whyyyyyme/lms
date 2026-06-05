@extends('layouts.app')

@section('title', 'Tambah Semester Mahasiswa')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $cancelUrl = Route::has('admin.semester.index')
        ? route('admin.semester.index')
        : (Route::has('admin.dashboard') ? route('admin.dashboard') : '#');
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Tambah Semester Mahasiswa</h1>

    <p>
        Buat semester baru untuk pengelompokan mahasiswa, mata kuliah praktikum,
        kelas praktikum, dan akses pembelajaran mahasiswa.
    </p>

    <div class="hero-actions">
        <a href="{{ $cancelUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('admin.semester.index'))
            <a href="{{ route('admin.semester.index') }}" class="btn btn-primary">
                🎓 Semua Semester
            </a>
        @endif
    </div>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Form Semester Mahasiswa</h2>
            <div class="section-subtitle">
                Isi nama semester, level semester, deskripsi, dan status aktif semester.
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.semester.store') }}" class="form-card">
        @csrf

        @include('admin.study-semesters._form')

        <div class="form-actions">
            <a href="{{ $cancelUrl }}" class="btn">
                Batal
            </a>

            <button type="submit" class="btn btn-primary">
                Simpan Semester
            </button>
        </div>
    </form>
</section>
@endsection