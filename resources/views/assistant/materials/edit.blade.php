@extends('layouts.app', ['title' => 'Edit Materi'])

@section('title', 'Edit Materi')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Asisten Praktikum',
    'title' => 'Edit Materi',
    'description' => 'Perbarui materi PDF atau link materi pembelajaran.'
])

<form
    action="{{ route('assistant.materi.update', $material) }}"
    method="POST"
    enctype="multipart/form-data"
    class="form-card"
>
    @csrf
    @method('PUT')

    @include('assistant.materials._form')

    @include('partials.form.actions', [
        'cancel' => route('assistant.materi.index'),
        'label' => 'Update Materi'
    ])
</form>
@endsection