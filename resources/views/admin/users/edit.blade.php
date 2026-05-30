@extends('layouts.app')

@php
    $currentRole = old('role', $user->roles->pluck('name')->first() ?: $user->role);

    $roleLabel = match ($currentRole) {
        'asisten' => 'Asisten Praktikum',
        'mahasiswa' => 'Mahasiswa',
        default => 'User',
    };
@endphp

@section('title', 'Edit ' . $roleLabel)

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Edit ' . $roleLabel,
    'description' => 'Perbarui akun asisten praktikum atau mahasiswa. Akun admin utama dikelola manual.'
])

<form action="{{ route('admin.users.update', $user) }}" method="POST" class="form-card">
    @csrf
    @method('PUT')

    @include('admin.users._form')

    @include('partials.form.actions', [
        'cancel' => route('admin.users.show', $user),
        'label' => 'Update User'
    ])
</form>
@endsection