@extends('layouts.app')

@section('title', 'Edit Kelas Praktikum')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Edit Kelas Praktikum',
    'description' => 'Perbarui mata kuliah, asisten, jadwal, ruangan, dan pembagian mahasiswa khusus.'
])

<form action="{{ route('admin.kelas.update', $praktikumClass) }}" method="POST" class="form-card">
    @csrf
    @method('PUT')

    @include('admin.classes._form')

    @include('partials.form.actions', [
        'cancel' => route('admin.kelas.show', $praktikumClass),
        'label' => 'Update Kelas'
    ])
</form>
@endsection