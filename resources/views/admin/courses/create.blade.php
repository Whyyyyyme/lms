@extends('layouts.app')

@section('title', 'Tambah Mata Kuliah')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $cancelUrl = Route::has('admin.matakuliah.index')
        ? route('admin.matakuliah.index')
        : (Route::has('admin.dashboard') ? route('admin.dashboard') : '#');
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Tambah Mata Kuliah</h1>

    <p>
        Buat mata kuliah praktikum baru dan hubungkan ke semester mahasiswa serta tahun akademik.
        Data ini nantinya menjadi dasar pembuatan kelas praktikum.
    </p>

    <div class="hero-actions">
        <a href="{{ $cancelUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('admin.matakuliah.index'))
            <a href="{{ route('admin.matakuliah.index') }}" class="btn btn-primary">
                📚 Semua Mata Kuliah
            </a>
        @endif
    </div>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Form Mata Kuliah</h2>
            <div class="section-subtitle">
                Isi kode, nama, semester mahasiswa, tahun akademik, SKS, dan status mata kuliah.
            </div>
        </div>
    </div>

    <form action="{{ route('admin.matakuliah.store') }}" method="POST" class="form-card">
        @csrf

        @include('admin.courses._form')

        <div class="form-actions">
            <a href="{{ $cancelUrl }}" class="btn">
                Batal
            </a>

            <button type="submit" class="btn btn-primary">
                Simpan Mata Kuliah
            </button>
        </div>
    </form>
</section>
@endsection