@extends('layouts.app')
@section('title', 'Tambah Matakuliah')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Tambah Matakuliah'])
<form action="{{ route('admin.matakuliah.store') }}" method="POST" class="form-card">@csrf @include('admin.courses._form') @include('partials.form.actions', ['cancel' => route('admin.matakuliah.index'), 'label' => 'Simpan Matakuliah'])</form>
@endsection
