@extends('layouts.app')

@section('title', 'Tambah Materi')

@section('content')
@php
    $selectedClass = $selectedClass ?? null;
    $cancelUrl = $selectedClass
        ? route('assistant.courses.show', $selectedClass)
        : route('assistant.courses.index');
@endphp

@include('partials.page-header', [
    'eyebrow' => 'Asisten',
    'title' => 'Tambah Materi',
    'description' => 'Unggah materi PDF atau tambahkan link materi pembelajaran.'
])

@if(($classes ?? collect())->isEmpty())
    <div class="alert alert-error">
        Kamu belum ditugaskan ke kelas mana pun. Minta admin mengatur kelas praktikum terlebih dahulu.
    </div>
@endif

<form action="{{ route('assistant.materi.store') }}" method="POST" enctype="multipart/form-data" class="form-card">
    @csrf

    @include('assistant.materials._form')

    @include('partials.form.actions', [
        'cancel' => $cancelUrl,
        'label' => 'Simpan Materi'
    ])
</form>
@endsection
