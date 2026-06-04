@extends('layouts.app')

@section('title', 'Edit Materi')

@section('content')
@php
    $selectedClass = $selectedClass ?? $material->kelas;
    $cancelUrl = $selectedClass
        ? route('assistant.courses.show', $selectedClass)
        : route('assistant.materi.index');
@endphp

@include('partials.page-header', [
    'eyebrow' => 'Asisten',
    'title' => 'Edit Materi',
    'description' => 'Perbarui materi PDF atau link materi pembelajaran.'
])

<form action="{{ route('assistant.materi.update', $material) }}" method="POST" enctype="multipart/form-data" class="form-card">
    @csrf
    @method('PUT')

    @include('assistant.materials._form')

    @include('partials.form.actions', [
        'cancel' => $cancelUrl,
        'label' => 'Update Materi'
    ])
</form>
@endsection
