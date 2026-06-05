@extends('layouts.app')

@section('title', 'Tambah Tahun Akademik')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $cancelUrl = Route::has('admin.tahun-akademik.index')
        ? route('admin.tahun-akademik.index')
        : (Route::has('admin.dashboard') ? route('admin.dashboard') : '#');
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Tambah Tahun Akademik</h1>

    <p>
        Buat periode tahun akademik baru untuk mata kuliah praktikum.
        Tahun akademik digunakan untuk membedakan periode ganjil dan genap.
    </p>

    <div class="hero-actions">
        <a href="{{ $cancelUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('admin.tahun-akademik.index'))
            <a href="{{ route('admin.tahun-akademik.index') }}" class="btn btn-primary">
                📅 Semua Tahun Akademik
            </a>
        @endif
    </div>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Form Tahun Akademik</h2>
            <div class="section-subtitle">
                Isi tahun, periode semester, dan status aktif tahun akademik.
            </div>
        </div>
    </div>

    <form action="{{ route('admin.tahun-akademik.store') }}" method="POST" class="form-card">
        @csrf

        @include('admin.academic-years._form')

        <div class="form-actions">
            <a href="{{ $cancelUrl }}" class="btn">
                Batal
            </a>

            <button type="submit" class="btn btn-primary">
                Simpan Tahun Akademik
            </button>
        </div>
    </form>
</section>
@endsection