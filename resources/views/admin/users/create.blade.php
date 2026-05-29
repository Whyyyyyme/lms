@extends('layouts.app')
@section('title', 'Tambah User')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Tambah User', 'description' => 'Tambahkan akun admin, asisten, atau mahasiswa.'])
<form action="{{ route('admin.users.store') }}" method="POST" class="form-card">
    @csrf
    @include('admin.users._form')
    @include('partials.form.actions', ['cancel' => route('admin.users.index'), 'label' => 'Simpan User'])
</form>
@endsection
