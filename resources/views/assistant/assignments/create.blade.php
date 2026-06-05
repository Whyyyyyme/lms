@extends('layouts.app')

@section('title', 'Buat Tugas')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $selectedClass = $selectedClass ?? null;
    $classes = $classes ?? collect();

    if ($selectedClass && Route::has('assistant.courses.show')) {
        $cancelUrl = route('assistant.courses.show', $selectedClass);
    } elseif (Route::has('assistant.courses.index')) {
        $cancelUrl = route('assistant.courses.index');
    } elseif (Route::has('assistant.tugas.index')) {
        $cancelUrl = route('assistant.tugas.index');
    } elseif (Route::has('assistant.dashboard')) {
        $cancelUrl = route('assistant.dashboard');
    } else {
        $cancelUrl = '#';
    }
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Asisten Praktikum</div>

    <h1>Buat Tugas</h1>

    <p>
        Buat tugas praktikum baru, tambahkan instruksi, unggah lampiran jika diperlukan,
        atur waktu publikasi, dan tentukan deadline pengumpulan mahasiswa.
    </p>

    <div class="hero-actions">
        <a href="{{ $cancelUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('assistant.tugas.index'))
            <a href="{{ route('assistant.tugas.index') }}" class="btn btn-primary">
                📝 Semua Tugas
            </a>
        @endif
    </div>
</section>

@if($classes->isEmpty() && ! $selectedClass)
    <div class="alert alert-error">
        Kamu belum ditugaskan ke kelas mana pun. Minta admin mengatur kelas praktikum terlebih dahulu.
    </div>
@endif

<div class="alert">
    <strong>Catatan file tugas:</strong>
    Format yang didukung adalah PDF, DOCX, TXT, MD, atau CSV.
    Hindari upload PPT, PPTX, ZIP, RAR, atau file scan/gambar jika ingin isi tugas bisa dibaca oleh AI.
</div>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Form Tugas Praktikum</h2>
            <div class="section-subtitle">
                Isi data tugas sesuai kelas praktikum, instruksi, nilai maksimal, dan deadline pengumpulan.
            </div>
        </div>
    </div>

    <form
        action="{{ route('assistant.tugas.store') }}"
        method="POST"
        enctype="multipart/form-data"
        class="form-card"
    >
        @csrf

        @include('assistant.assignments._form')

        <div class="form-group">
            <label for="published_at" class="form-label">
                Waktu Publikasi
            </label>

            <input
                type="datetime-local"
                id="published_at"
                name="published_at"
                value="{{ old('published_at') }}"
                class="form-control"
            >

            <p class="form-help">
                Kosongkan jika tugas ingin langsung ditampilkan ke mahasiswa.
                Jika diisi, tugas baru akan muncul sesuai waktu publikasi ini.
            </p>

            @error('published_at')
                <div class="form-help" style="color: var(--danger);">
                    {{ $message }}
                </div>
            @enderror
        </div>

        <div class="form-actions">
            <a href="{{ $cancelUrl }}" class="btn">
                Batal
            </a>

            <button
                type="submit"
                class="btn btn-primary"
                @disabled($classes->isEmpty() && ! $selectedClass)
            >
                Simpan Tugas
            </button>
        </div>
    </form>
</section>
@endsection