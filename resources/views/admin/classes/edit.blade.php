@extends('layouts.app')

@section('title', 'Edit Kelas Praktikum')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    if (Route::has('admin.kelas.show')) {
        $cancelUrl = route('admin.kelas.show', $praktikumClass);
    } elseif (Route::has('admin.kelas.index')) {
        $cancelUrl = route('admin.kelas.index');
    } elseif (Route::has('admin.dashboard')) {
        $cancelUrl = route('admin.dashboard');
    } else {
        $cancelUrl = '#';
    }
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Edit Kelas Praktikum</h1>

    <p>
        Perbarui mata kuliah, asisten, tipe kelas, rombel, jadwal, ruangan,
        status kelas, dan pembagian mahasiswa khusus.
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

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Form Edit Kelas Praktikum</h2>
            <div class="section-subtitle">
                Ubah data kelas praktikum sesuai kebutuhan mata kuliah, jadwal, ruangan, asisten, dan mahasiswa.
            </div>
        </div>

        <span class="status-pill {{ $praktikumClass->is_active ? 'status-success' : 'status-danger' }}">
            {{ $praktikumClass->is_active ? 'Aktif' : 'Nonaktif' }}
        </span>
    </div>

    <form action="{{ route('admin.kelas.update', $praktikumClass) }}" method="POST" class="form-card">
        @csrf
        @method('PUT')

        @include('admin.classes._form')

        <div class="form-actions">
            <a href="{{ $cancelUrl }}" class="btn">
                Batal
            </a>

            <button type="submit" class="btn btn-primary">
                Update Kelas
            </button>
        </div>
    </form>
</section>
@endsection