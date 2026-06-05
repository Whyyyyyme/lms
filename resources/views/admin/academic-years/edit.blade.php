@extends('layouts.app')

@section('title', 'Edit Tahun Akademik')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    if (Route::has('admin.tahun-akademik.show')) {
        $cancelUrl = route('admin.tahun-akademik.show', $academicYear);
    } elseif (Route::has('admin.tahun-akademik.index')) {
        $cancelUrl = route('admin.tahun-akademik.index');
    } elseif (Route::has('admin.dashboard')) {
        $cancelUrl = route('admin.dashboard');
    } else {
        $cancelUrl = '#';
    }
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Edit Tahun Akademik</h1>

    <p>
        Perbarui periode tahun akademik, semester ganjil/genap, dan status aktif.
        Jika tahun akademik ini diaktifkan, tahun akademik lain akan menjadi nonaktif sesuai logic sistem.
    </p>

    <div class="hero-actions">
        <a href="{{ $cancelUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('admin.tahun-akademik.index'))
            <a href="{{ route('admin.tahun-akademik.index') }}" class="btn btn-primary">
                📅 Semua Tahun Akademik
            </a>
        @endif
    </div>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Form Edit Tahun Akademik</h2>
            <div class="section-subtitle">
                Ubah tahun akademik, periode, dan status aktif sesuai kebutuhan data akademik.
            </div>
        </div>

        <span class="status-pill {{ $academicYear->is_active ? 'status-success' : 'status-danger' }}">
            {{ $academicYear->is_active ? 'Aktif' : 'Nonaktif' }}
        </span>
    </div>

    <form action="{{ route('admin.tahun-akademik.update', $academicYear) }}" method="POST" class="form-card">
        @csrf
        @method('PUT')

        @include('admin.academic-years._form')

        <div class="form-actions">
            <a href="{{ $cancelUrl }}" class="btn">
                Batal
            </a>

            <button type="submit" class="btn btn-primary">
                Update Tahun Akademik
            </button>
        </div>
    </form>
</section>
@endsection