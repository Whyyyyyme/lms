@extends('layouts.app')

@section('title', 'Tambah Mata Kuliah')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Tambah Mata Kuliah',
    'description' => 'Buat mata kuliah baru dan hubungkan ke semester mahasiswa.'
])

<form action="{{ route('admin.matakuliah.store') }}" method="POST" class="form-card">
    @csrf

    @include('admin.courses._form')

    @include('partials.form.actions', [
        'cancel' => route('admin.matakuliah.index'),
        'label' => 'Simpan Mata Kuliah'
    ])
</form>
@endsection