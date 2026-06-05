@extends('layouts.app')

@section('title', 'Edit Mata Kuliah')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    if (Route::has('admin.matakuliah.show')) {
        $cancelUrl = route('admin.matakuliah.show', $course);
    } elseif (Route::has('admin.matakuliah.index')) {
        $cancelUrl = route('admin.matakuliah.index');
    } elseif (Route::has('admin.dashboard')) {
        $cancelUrl = route('admin.dashboard');
    } else {
        $cancelUrl = '#';
    }
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Edit Mata Kuliah</h1>

    <p>
        Perbarui data mata kuliah, semester mahasiswa, tahun akademik, SKS, kode mata kuliah,
        dan status aktif mata kuliah.
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
            <h2 class="section-title">Form Edit Mata Kuliah</h2>
            <div class="section-subtitle">
                Ubah informasi mata kuliah sesuai kebutuhan data akademik dan kelas praktikum.
            </div>
        </div>

        <span class="status-pill {{ $course->is_active ? 'status-success' : 'status-danger' }}">
            {{ $course->is_active ? 'Aktif' : 'Nonaktif' }}
        </span>
    </div>

    <form action="{{ route('admin.matakuliah.update', $course) }}" method="POST" class="form-card">
        @csrf
        @method('PUT')

        @include('admin.courses._form')

        <div class="form-actions">
            <a href="{{ $cancelUrl }}" class="btn">
                Batal
            </a>

            <button type="submit" class="btn btn-primary">
                Update Mata Kuliah
            </button>
        </div>
    </form>
</section>
@endsection