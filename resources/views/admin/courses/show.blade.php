@extends('layouts.app')

@section('title', 'Detail Mata Kuliah')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $safe = fn (string $name, array $params = []) => Route::has($name)
        ? route($name, $params)
        : '#';

    $classes = $course->classes ?? collect();
    $classesCount = (int) ($course->classes_count ?? $classes->count());
    $semesterStudentsCount = $course->studySemester?->students?->count() ?? 0;

    $backUrl = Route::has('admin.matakuliah.index')
        ? route('admin.matakuliah.index')
        : (Route::has('admin.dashboard') ? route('admin.dashboard') : '#');

    $canDelete = $classesCount === 0;

    $academicYearText = $course->academicYear
        ? $course->academicYear->year . ' - ' . ucfirst($course->academicYear->semester)
        : '-';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Detail Mata Kuliah</h1>

    <p>
        Detail mata kuliah, semester mahasiswa, tahun akademik, SKS, status,
        dan kelas praktikum yang terhubung.
    </p>

    <div class="hero-actions">
        <a href="{{ $backUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('admin.matakuliah.edit'))
            <a href="{{ route('admin.matakuliah.edit', $course) }}" class="btn btn-primary">
                Edit Mata Kuliah
            </a>
        @endif
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">
                {{ $course->name }}
            </h2>

            <div class="section-subtitle">
                Ringkasan informasi utama mata kuliah praktikum.
            </div>
        </div>

        <span class="status-pill {{ $course->is_active ? 'status-success' : 'status-danger' }}">
            {{ $course->is_active ? 'Aktif' : 'Nonaktif' }}
        </span>
    </div>

    <div class="grid grid-4">
        <div class="stat-card">
            <div class="stat-label">Kode</div>
            <div class="stat-value" style="font-size: 24px;">
                {{ $course->code ?? '-' }}
            </div>
            <div class="stat-note">
                Kode identitas mata kuliah.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Nama Mata Kuliah</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $course->name }}
            </div>
            <div class="stat-note">
                Nama yang tampil untuk admin, asisten, dan mahasiswa.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">SKS</div>
            <div class="stat-value">
                {{ $course->sks ?? '-' }}
            </div>
            <div class="stat-note">
                Jumlah SKS mata kuliah.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Status</div>
            <div class="stat-value" style="font-size: 24px;">
                {{ $course->is_active ? 'Aktif' : 'Nonaktif' }}
            </div>
            <div class="stat-note">
                Status penggunaan mata kuliah.
            </div>
        </div>
    </div>

    <div class="grid grid-4" style="margin-top: 18px;">
        <div class="stat-card">
            <div class="stat-label">Semester Mahasiswa</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $course->studySemester?->name ?? '-' }}
            </div>
            <div class="stat-note">
                Mahasiswa semester ini dapat melihat mata kuliah.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Tahun Akademik</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $academicYearText }}
            </div>
            <div class="stat-note">
                Periode penyelenggaraan mata kuliah.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Mahasiswa Semester</div>
            <div class="stat-value">
                {{ $semesterStudentsCount }}
            </div>
            <div class="stat-note">
                Jumlah mahasiswa pada semester terkait.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Kelas Praktikum</div>
            <div class="stat-value">
                {{ $classesCount }}
            </div>
            <div class="stat-note">
                Total kelas praktikum pada mata kuliah ini.
            </div>
        </div>
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Kelas Praktikum</h2>
            <div class="section-subtitle">
                Daftar kelas praktikum yang terhubung dengan mata kuliah ini.
            </div>
        </div>

        @if(Route::has('admin.kelas.create'))
            <a href="{{ route('admin.kelas.create') }}" class="btn btn-primary btn-sm">
                + Tambah Kelas
            </a>
        @endif
    </div>

    @if($classes->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">🏫</div>

            <h3 class="empty-state-title">
                Belum ada kelas praktikum
            </h3>

            <p class="empty-state-text">
                Belum ada kelas praktikum untuk mata kuliah ini.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Kelas Praktikum</th>
                            <th>Asisten</th>
                            <th>Ruang</th>
                            <th>Jadwal</th>
                            <th>Mahasiswa</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($classes as $class)
                            <tr>
                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>{{ $class->name }}</strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ $course->code ?? '-' }} · {{ $course->name }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $class->assistant?->name ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill status-muted">
                                        {{ $class->room ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $class->schedule ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <span class="status-pill status-info">
                                            {{ $class->resolved_students_count ?? 0 }} Mahasiswa
                                        </span>

                                        <span class="item-meta" style="margin-top: 0;">
                                            Manual: {{ $class->students->count() }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="status-pill {{ $class->is_active ? 'status-success' : 'status-danger' }}">
                                        {{ $class->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>

                                <td>
                                    <a class="btn btn-sm" href="{{ $safe('admin.kelas.show', [$class]) }}">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Aksi Mata Kuliah</h2>
            <div class="section-subtitle">
                Kembali ke daftar mata kuliah, edit data, atau hapus jika belum memiliki kelas praktikum.
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a class="btn" href="{{ $backUrl }}">
            Kembali
        </a>

        @if(Route::has('admin.matakuliah.edit'))
            <a class="btn btn-primary" href="{{ route('admin.matakuliah.edit', $course) }}">
                Edit Mata Kuliah
            </a>
        @endif

        @if($canDelete && Route::has('admin.matakuliah.destroy'))
            @include('partials.delete-button', [
                'action' => route('admin.matakuliah.destroy', $course),
                'confirm' => 'Yakin ingin menghapus mata kuliah ini?'
            ])
        @endif
    </div>
</section>

<div class="alert" style="margin-top: 18px;">
    <strong>Catatan:</strong>
    Jumlah mahasiswa semester berasal dari semester yang dipilih.
    Mahasiswa manual adalah mahasiswa yang dimasukkan khusus melalui relasi kelas.
</div>
@endsection