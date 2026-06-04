@extends('layouts.app')

@section('title', 'Buat Tugas')

@section('content')
@php
    $selectedClass = $selectedClass ?? null;
    $cancelUrl = $selectedClass
        ? route('assistant.courses.show', $selectedClass)
        : route('assistant.courses.index');
@endphp

@include('partials.page-header', [
    'eyebrow' => 'Asisten',
    'title' => 'Buat Tugas',
    'description' => 'Buat tugas baru, atur waktu publikasi, dan tentukan deadline pengumpulan.'
])

@if(($classes ?? collect())->isEmpty())
    <div class="alert alert-error">
        Kamu belum ditugaskan ke kelas mana pun. Minta admin mengatur kelas praktikum terlebih dahulu.
    </div>
@endif

<form action="{{ route('assistant.tugas.store') }}" method="POST" enctype="multipart/form-data" class="form-card">
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
            class="form-control @error('published_at') is-invalid @enderror"
        >

        <p class="form-help">
            Kosongkan jika tugas ingin langsung ditampilkan ke mahasiswa. Jika diisi, tugas baru akan muncul sesuai waktu publikasi ini.
        </p>

        @error('published_at')
            <div class="form-error">
                {{ $message }}
            </div>
        @enderror
    </div>

    @include('partials.form.actions', [
        'cancel' => $cancelUrl,
        'label' => 'Simpan Tugas'
    ])
</form>
@endsection
