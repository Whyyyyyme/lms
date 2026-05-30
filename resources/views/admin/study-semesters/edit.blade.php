@extends('layouts.app')

@section('title', 'Edit Semester Mahasiswa')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Edit Semester Mahasiswa',
    'description' => 'Perbarui data semester mahasiswa.'
])

<form method="POST" action="{{ route('admin.semester.update', $studySemester) }}" class="form-card">
    @csrf
    @method('PUT')

    @include('admin.study-semesters._form')

    @include('partials.form.actions', [
        'cancel' => route('admin.semester.show', $studySemester),
        'label' => 'Update Semester'
    ])
</form>
@endsection