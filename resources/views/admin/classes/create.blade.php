@extends('layouts.app')
@section('title', 'Tambah Kelas Praktikum')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Tambah Kelas Praktikum'])
@if(($courses ?? collect())->isEmpty())<div class="alert alert-error">Buat matakuliah terlebih dahulu sebelum membuat kelas.</div>@endif
<form action="{{ route('admin.kelas.store') }}" method="POST" class="form-card">@csrf @include('admin.classes._form') @include('partials.form.actions', ['cancel' => route('admin.kelas.index'), 'label' => 'Simpan Kelas'])</form>
@endsection
