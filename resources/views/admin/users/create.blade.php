@extends('layouts.app')
@section('title', 'Tambah User')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Tambah Asisten / Mahasiswa', 'description' => 'Tambahkan akun asisten praktikum atau mahasiswa. Akun admin utama dikelola manual.'])
<form action="{{ route('admin.users.store') }}" method="POST" class="form-card">
    @csrf
    @include('admin.users._form')
    @include('partials.form.actions', ['cancel' => route('admin.users.index'), 'label' => 'Simpan User'])
</form>
@endsection
