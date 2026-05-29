@extends('layouts.app')
@section('title', 'Edit Materi')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Edit Materi'])
<form action="{{ route('assistant.materi.update', $material) }}" method="POST" enctype="multipart/form-data" class="form-card">
    @csrf
    @method('PUT')
    @include('assistant.materials._form')
    @include('partials.form.actions', ['cancel' => route('assistant.materi.index'), 'label' => 'Update Materi'])
</form>
@endsection
