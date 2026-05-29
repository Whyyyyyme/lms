@extends('layouts.app')
@section('title', 'Detail User')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Detail User'])
<div class="form-card">
    <p><strong>Nama:</strong> {{ $user->name }}</p>
    <p><strong>Email:</strong> {{ $user->email }}</p>
    <p><strong>NIM/NIP:</strong> {{ $user->nim_nip ?? '-' }}</p>
    <p><strong>Role:</strong> {{ $user->roles->pluck('name')->join(', ') ?: ($user->role ?? '-') }}</p>
    <p><strong>Status:</strong> {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</p>
    <div class="form-actions"><a class="btn" href="{{ route('admin.users.index') }}">Kembali</a><a class="btn btn-primary" href="{{ route('admin.users.edit', $user) }}">Edit</a></div>
</div>
@endsection
