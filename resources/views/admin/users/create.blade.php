@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $cancelUrl = Route::has('admin.users.index')
        ? route('admin.users.index')
        : (Route::has('admin.dashboard') ? route('admin.dashboard') : '#');
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Tambah Asisten / Mahasiswa</h1>

    <p>
        Tambahkan akun asisten praktikum atau mahasiswa ke dalam LMS Praktikum.
        Akun admin utama tetap dikelola manual agar akses sistem lebih aman.
    </p>

    <div class="hero-actions">
        <a href="{{ $cancelUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('admin.users.index'))
            <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                👥 Semua User
            </a>
        @endif
    </div>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Form Tambah User</h2>
            <div class="section-subtitle">
                Isi data akun, pilih jenis akun, lalu lengkapi semester dan rombel jika user adalah mahasiswa.
            </div>
        </div>
    </div>

    <form action="{{ route('admin.users.store') }}" method="POST" class="form-card">
        @csrf

        @include('admin.users._form')

        <div class="form-actions">
            <a href="{{ $cancelUrl }}" class="btn">
                Batal
            </a>

            <button type="submit" class="btn btn-primary">
                Simpan User
            </button>
        </div>
    </form>
</section>
@endsection