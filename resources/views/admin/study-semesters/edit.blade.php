@extends('layouts.app')

@section('title', 'Edit Semester Mahasiswa')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    if (Route::has('admin.semester.show')) {
        $cancelUrl = route('admin.semester.show', $studySemester);
    } elseif (Route::has('admin.semester.index')) {
        $cancelUrl = route('admin.semester.index');
    } elseif (Route::has('admin.dashboard')) {
        $cancelUrl = route('admin.dashboard');
    } else {
        $cancelUrl = '#';
    }
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Edit Semester Mahasiswa</h1>

    <p>
        Perbarui data semester mahasiswa, level semester, deskripsi, dan status aktif semester.
        Perubahan ini dapat memengaruhi pilihan semester pada form mahasiswa dan mata kuliah baru.
    </p>

    <div class="hero-actions">
        <a href="{{ $cancelUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('admin.semester.index'))
            <a href="{{ route('admin.semester.index') }}" class="btn btn-primary">
                🎓 Semua Semester
            </a>
        @endif
    </div>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Form Edit Semester</h2>
            <div class="section-subtitle">
                Ubah informasi semester mahasiswa sesuai kebutuhan data akademik.
            </div>
        </div>

        <span class="status-pill {{ $studySemester->is_active ? 'status-success' : 'status-danger' }}">
            {{ $studySemester->is_active ? 'Aktif' : 'Nonaktif' }}
        </span>
    </div>

    <form method="POST" action="{{ route('admin.semester.update', $studySemester) }}" class="form-card">
        @csrf
        @method('PUT')

        @include('admin.study-semesters._form')

        <div class="form-actions">
            <a href="{{ $cancelUrl }}" class="btn">
                Batal
            </a>

            <button type="submit" class="btn btn-primary">
                Update Semester
            </button>
        </div>
    </form>
</section>
@endsection