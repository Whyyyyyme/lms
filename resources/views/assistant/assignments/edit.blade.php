@extends('layouts.app')
@section('title', 'Edit Tugas')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Edit Tugas'])
<form action="{{ route('assistant.tugas.update', $assignment) }}" method="POST" enctype="multipart/form-data" class="form-card">
    @csrf
    @method('PUT')
    @include('assistant.assignments._form')
    @include('partials.form.actions', ['cancel' => route('assistant.tugas.index'), 'label' => 'Update Tugas'])
</form>
@endsection
