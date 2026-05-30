@extends('layouts.app')

@section('title', 'Edit Mata Kuliah')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Edit Mata Kuliah',
    'description' => 'Perbarui data mata kuliah dan relasi semesternya.'
])

<form action="{{ route('admin.matakuliah.update', $course) }}" method="POST" class="form-card">
    @csrf
    @method('PUT')

    @include('admin.courses._form')

    @include('partials.form.actions', [
        'cancel' => route('admin.matakuliah.show', $course),
        'label' => 'Update Mata Kuliah'
    ])
</form>
@endsection