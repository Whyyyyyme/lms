@extends('layouts.app')

@section('title', 'Tambah Materi')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $selectedClass = $selectedClass ?? null;
    $classes = $classes ?? collect();

    if ($selectedClass && Route::has('assistant.courses.show')) {
        $cancelUrl = route('assistant.courses.show', $selectedClass);
    } elseif (Route::has('assistant.courses.index')) {
        $cancelUrl = route('assistant.courses.index');
    } elseif (Route::has('assistant.materi.index')) {
        $cancelUrl = route('assistant.materi.index');
    } else {
        $cancelUrl = Route::has('assistant.dashboard')
            ? route('assistant.dashboard')
            : '#';
    }
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Asisten Praktikum</div>

    <h1>Tambah Materi</h1>

    <p>
        Unggah materi praktikum atau tambahkan link pembelajaran agar dapat diakses mahasiswa
        sesuai kelas praktikum yang kamu kelola.
    </p>

    <div class="hero-actions">
        <a href="{{ $cancelUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('assistant.materi.index'))
            <a href="{{ route('assistant.materi.index') }}" class="btn btn-primary">
                📘 Semua Materi
            </a>
        @endif
    </div>
</section>

@if($classes->isEmpty() && ! $selectedClass)
    <div class="alert alert-error">
        Kamu belum ditugaskan ke kelas mana pun. Minta admin mengatur kelas praktikum terlebih dahulu.
    </div>
@endif

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Form Materi Praktikum</h2>
            <div class="section-subtitle">
                Isi data materi sesuai kelas praktikum, tipe materi, dan status publikasinya.
            </div>
        </div>
    </div>

    <form
        action="{{ route('assistant.materi.store') }}"
        method="POST"
        enctype="multipart/form-data"
        class="form-card"
    >
        @csrf

        @include('assistant.materials._form')

        <div class="form-actions">
            <a href="{{ $cancelUrl }}" class="btn">
                Batal
            </a>

            <button
                type="submit"
                class="btn btn-primary"
                @disabled($classes->isEmpty() && ! $selectedClass)
            >
                Simpan Materi
            </button>
        </div>
    </form>
</section>
@endsection