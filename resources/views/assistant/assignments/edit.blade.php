@extends('layouts.app')

@section('title', 'Edit Tugas')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $selectedClass = $selectedClass ?? $assignment->kelas;

    if ($selectedClass && Route::has('assistant.courses.show')) {
        $cancelUrl = route('assistant.courses.show', $selectedClass);
    } elseif (Route::has('assistant.tugas.index')) {
        $cancelUrl = route('assistant.tugas.index');
    } elseif (Route::has('assistant.dashboard')) {
        $cancelUrl = route('assistant.dashboard');
    } else {
        $cancelUrl = '#';
    }

    $publishedAtValue = old(
        'published_at',
        $assignment->published_at
            ? $assignment->published_at->timezone(config('app.timezone', 'Asia/Jakarta'))->format('Y-m-d\TH:i')
            : ''
    );
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Asisten Praktikum</div>

    <h1>Edit Tugas</h1>

    <p>
        Perbarui data tugas praktikum, instruksi, deadline, file lampiran,
        waktu publikasi, dan nilai maksimal sesuai kebutuhan kelas.
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

<div class="alert">
    <strong>Catatan file tugas:</strong>
    Format yang didukung adalah PDF, DOCX, TXT, MD, atau CSV.
    Jika mengganti file, hindari upload PPT, PPTX, ZIP, RAR, atau file scan/gambar jika ingin isi tugas bisa dibaca oleh AI.
</div>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Form Edit Tugas</h2>
            <div class="section-subtitle">
                Ubah data tugas, file instruksi, deadline, nilai maksimal, dan waktu publikasi.
            </div>
        </div>

        @if($assignment->published_at)
            @if($assignment->published_at->isFuture())
                <span class="status-pill status-warning">
                    Terjadwal
                </span>
            @else
                <span class="status-pill status-success">
                    Dipublikasikan
                </span>
            @endif
        @else
            <span class="status-pill status-success">
                Langsung Tampil
            </span>
        @endif
    </div>

    <form
        action="{{ route('assistant.tugas.update', $assignment) }}"
        method="POST"
        enctype="multipart/form-data"
        class="form-card"
    >
        @csrf
        @method('PUT')

        @include('assistant.assignments._form')

        <div class="form-group">
            <label for="published_at" class="form-label">
                Waktu Publikasi
            </label>

            <input
                type="datetime-local"
                id="published_at"
                name="published_at"
                value="{{ $publishedAtValue }}"
                class="form-control"
            >

            <p class="form-help">
                Kosongkan jika tugas ingin langsung ditampilkan ke mahasiswa.
                Jika diisi dengan waktu masa depan, tugas baru akan muncul sesuai waktu publikasi ini.
            </p>

            @if($assignment->published_at)
                @if($assignment->published_at->isFuture())
                    <div class="alert" style="margin-top: 10px; margin-bottom: 0;">
                        <strong>Status saat ini:</strong>
                        tugas masih terjadwal dan akan tampil pada
                        {{ $assignment->published_at->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d/m/Y H:i') }} WIB.
                    </div>
                @else
                    <div class="alert alert-success" style="margin-top: 10px; margin-bottom: 0;">
                        <strong>Status saat ini:</strong>
                        tugas sudah dipublikasikan sejak
                        {{ $assignment->published_at->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d/m/Y H:i') }} WIB.
                    </div>
                @endif
            @else
                <div class="alert alert-success" style="margin-top: 10px; margin-bottom: 0;">
                    <strong>Status saat ini:</strong>
                    tugas langsung tampil ke mahasiswa.
                </div>
            @endif

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

            <button type="submit" class="btn btn-primary">
                Update Tugas
            </button>
        </div>
    </form>
</section>
@endsection