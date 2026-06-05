@extends('layouts.app')

@section('title', 'Buat Pengumuman')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $classes = $classes ?? collect();

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

    <h1>Buat Pengumuman</h1>

    <p>
        Kirim pengumuman kepada mahasiswa pada kelas praktikum tertentu.
        Gunakan halaman ini untuk menyampaikan informasi penting terkait materi, tugas, absensi, atau jadwal praktikum.
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

@if($classes->isEmpty())
    <div class="alert alert-error">
        Kamu belum ditugaskan ke kelas mana pun. Minta admin mengatur kelas praktikum terlebih dahulu.
    </div>
@endif

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Form Pengumuman</h2>
            <div class="section-subtitle">
                Pilih kelas praktikum, tulis judul, lalu isi pengumuman yang akan dibaca mahasiswa.
            </div>
        </div>
    </div>

    <form
        action="{{ route('assistant.pengumuman.store') }}"
        method="POST"
        class="form-card"
    >
        @csrf

        @include('assistant.announcements._form')

        <div class="form-actions">
            <a href="{{ $cancelUrl }}" class="btn">
                Batal
            </a>

            <button
                type="submit"
                class="btn btn-primary"
                @disabled($classes->isEmpty())
            >
                Kirim Pengumuman
            </button>
        </div>
    </form>
</section>
@endsection