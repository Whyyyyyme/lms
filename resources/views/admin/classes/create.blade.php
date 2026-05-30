@extends('layouts.app')

@section('title', 'Tambah Kelas Praktikum')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Tambah Kelas Praktikum',
    'description' => 'Buat kelas praktikum untuk mata kuliah tertentu dan hubungkan dengan asisten.'
])

@if(($courses ?? collect())->isEmpty())
    <div class="alert alert-error">
        Buat mata kuliah terlebih dahulu sebelum membuat kelas praktikum.
    </div>
@endif

<form action="{{ route('admin.kelas.store') }}" method="POST" class="form-card">
    @csrf

    @include('admin.classes._form')

    @include('partials.form.actions', [
        'cancel' => route('admin.kelas.index'),
        'label' => 'Simpan Kelas'
    ])
</form>
@endsection