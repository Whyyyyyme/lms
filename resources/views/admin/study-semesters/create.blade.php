@extends('layouts.app')

@section('title', 'Tambah Semester Mahasiswa')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Tambah Semester Mahasiswa',
    'description' => 'Buat semester baru untuk pengelompokan mahasiswa dan mata kuliah.'
])

<form method="POST" action="{{ route('admin.semester.store') }}" class="form-card">
    @csrf

    @include('admin.study-semesters._form')

    @include('partials.form.actions', [
        'cancel' => route('admin.semester.index'),
        'label' => 'Simpan Semester'
    ])
</form>
@endsection