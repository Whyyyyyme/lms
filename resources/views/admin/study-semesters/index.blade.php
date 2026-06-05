@extends('layouts.app')

@section('title', 'Kelola Semester Mahasiswa')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $studySemesters = $studySemesters ?? collect();

    $dashboardUrl = Route::has('admin.dashboard')
        ? route('admin.dashboard')
        : '#';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Kelola Semester Mahasiswa</h1>

    <p>
        Semester menjadi dasar pengelompokan mahasiswa, mata kuliah praktikum,
        kelas praktikum, dan riwayat enrollment mahasiswa.
    </p>

    <div class="hero-actions">
        <a href="{{ $dashboardUrl }}" class="btn">
            ← Dashboard
        </a>

        <a href="{{ route('admin.semester.create') }}" class="btn btn-primary">
            + Tambah Semester
        </a>
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Filter Semester</h2>
            <div class="section-subtitle">
                Cari semester berdasarkan nama semester atau deskripsi.
            </div>
        </div>
    </div>

    <form method="GET" class="actions-inline">
        <input
            class="form-control"
            style="width: 280px;"
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari semester"
        >

        <button class="btn" type="submit">
            Filter
        </button>

        @if(request()->filled('search'))
            <a href="{{ route('admin.semester.index') }}" class="btn">
                Reset
            </a>
        @endif
    </form>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Semester Mahasiswa</h2>
            <div class="section-subtitle">
                Kelola level semester, jumlah mata kuliah, mahasiswa, riwayat enrollment, dan status aktif semester.
            </div>
        </div>

        <a href="{{ route('admin.semester.create') }}" class="btn btn-primary btn-sm">
            + Tambah Semester
        </a>
    </div>

    @if($studySemesters->count() === 0)
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">🎓</div>

            <h3 class="empty-state-title">
                Belum ada semester mahasiswa
            </h3>

            <p class="empty-state-text">
                Data semester akan tampil setelah admin menambahkan semester mahasiswa.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Level</th>
                            <th>Nama Semester</th>
                            <th>Mata Kuliah</th>
                            <th>Mahasiswa</th>
                            <th>Riwayat Enrollment</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($studySemesters as $semester)
                            <tr>
                                <td>
                                    <span class="status-pill status-muted">
                                        Level {{ $semester->level }}
                                    </span>
                                </td>

                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $semester->name }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ $semester->description ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="status-pill status-info">
                                        {{ $semester->courses_count }} Mata Kuliah
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill status-success">
                                        {{ $semester->students_count }} Mahasiswa
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill status-muted">
                                        {{ $semester->enrollments_count }} Riwayat
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill {{ $semester->is_active ? 'status-success' : 'status-danger' }}">
                                        {{ $semester->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="actions-inline">
                                        <a class="btn btn-sm" href="{{ route('admin.semester.show', $semester) }}">
                                            Detail
                                        </a>

                                        <a class="btn btn-sm" href="{{ route('admin.semester.edit', $semester) }}">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 18px;">
            {{ $studySemesters->links() }}
        </div>
    @endif
</section>
@endsection