@extends('layouts.app')

@section('title', 'Edit Materi')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $selectedClass = $selectedClass ?? $material->kelas;

    if ($selectedClass && Route::has('assistant.courses.show')) {
        $cancelUrl = route('assistant.courses.show', $selectedClass);
    } elseif (Route::has('assistant.materi.index')) {
        $cancelUrl = route('assistant.materi.index');
    } elseif (Route::has('assistant.dashboard')) {
        $cancelUrl = route('assistant.dashboard');
    } else {
        $cancelUrl = '#';
    }
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Asisten Praktikum</div>

    <h1>Edit Materi</h1>

    <p>
        Perbarui materi praktikum yang sudah dibuat, baik berupa file PDF maupun link pembelajaran.
        Pastikan materi sesuai dengan kelas praktikum yang dipilih.
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

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Form Edit Materi</h2>
            <div class="section-subtitle">
                Ubah data materi, tipe materi, file/link, dan waktu publikasi.
            </div>
        </div>

        @if($material->type)
            <span class="status-pill status-info">
                {{ strtoupper($material->type) }}
            </span>
        @endif
    </div>

    <form
        action="{{ route('assistant.materi.update', $material) }}"
        method="POST"
        enctype="multipart/form-data"
        class="form-card"
    >
        @csrf
        @method('PUT')

        @include('assistant.materials._form')

        <div class="form-actions">
            <a href="{{ $cancelUrl }}" class="btn">
                Batal
            </a>

            <button type="submit" class="btn btn-primary">
                Update Materi
            </button>
        </div>
    </form>
</section>
@endsection