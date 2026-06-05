@extends('layouts.app')

@section('title', 'Edit Pengumuman')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    if (Route::has('assistant.pengumuman.index')) {
        $cancelUrl = route('assistant.pengumuman.index');
    } elseif (Route::has('assistant.dashboard')) {
        $cancelUrl = route('assistant.dashboard');
    } else {
        $cancelUrl = '#';
    }
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Asisten Praktikum</div>

    <h1>Edit Pengumuman</h1>

    <p>
        Perbarui pengumuman yang sudah dibuat untuk mahasiswa kelas praktikum.
        Pastikan informasi yang disampaikan sudah jelas dan sesuai dengan kelas yang dipilih.
    </p>

    <div class="hero-actions">
        <a href="{{ $cancelUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('assistant.pengumuman.index'))
            <a href="{{ route('assistant.pengumuman.index') }}" class="btn btn-primary">
                📢 Semua Pengumuman
            </a>
        @endif
    </div>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Form Edit Pengumuman</h2>
            <div class="section-subtitle">
                Ubah kelas tujuan, judul, atau isi pengumuman.
            </div>
        </div>
    </div>

    <form
        action="{{ route('assistant.pengumuman.update', $announcement) }}"
        method="POST"
        class="form-card"
    >
        @csrf
        @method('PUT')

        @include('assistant.announcements._form')

        <div class="form-actions">
            <a href="{{ $cancelUrl }}" class="btn">
                Batal
            </a>

            <button type="submit" class="btn btn-primary">
                Update Pengumuman
            </button>
        </div>
    </form>
</section>
@endsection