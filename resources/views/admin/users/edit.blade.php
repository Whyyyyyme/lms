@extends('layouts.app')

@php
    use Illuminate\Support\Facades\Route;

    $currentRole = old('role', $user->roles->pluck('name')->first() ?: $user->role);

    $roleLabel = match ($currentRole) {
        'asisten' => 'Asisten Praktikum',
        'mahasiswa' => 'Mahasiswa',
        default => 'User',
    };

    if (Route::has('admin.users.show')) {
        $cancelUrl = route('admin.users.show', $user);
    } elseif (Route::has('admin.users.index')) {
        $cancelUrl = route('admin.users.index');
    } elseif (Route::has('admin.dashboard')) {
        $cancelUrl = route('admin.dashboard');
    } else {
        $cancelUrl = '#';
    }
@endphp

@section('title', 'Edit ' . $roleLabel)

@section('content')
<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Edit {{ $roleLabel }}</h1>

    <p>
        Perbarui data akun asisten praktikum atau mahasiswa.
        Akun admin utama tetap dikelola manual agar akses sistem lebih aman.
    </p>

    <div class="hero-actions">
        <a href="{{ $cancelUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('admin.users.index'))
            <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
                👥 Semua User
            </a>
        @endif
    </div>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Form Edit {{ $roleLabel }}</h2>
            <div class="section-subtitle">
                Ubah data akun, jenis akun, status aktif, password, semester, atau rombel jika diperlukan.
            </div>
        </div>

        <span class="status-pill {{ $user->is_active ? 'status-success' : 'status-danger' }}">
            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
        </span>
    </div>

    <form action="{{ route('admin.users.update', $user) }}" method="POST" class="form-card">
        @csrf
        @method('PUT')

        @include('admin.users._form')

        <div class="form-actions">
            <a href="{{ $cancelUrl }}" class="btn">
                Batal
            </a>

            <button type="submit" class="btn btn-primary">
                Update User
            </button>
        </div>
    </form>
</section>
@endsection