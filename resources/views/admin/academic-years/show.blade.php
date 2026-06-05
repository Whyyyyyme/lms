@extends('layouts.app')

@section('title', 'Detail Tahun Akademik')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $safe = fn (string $name, array $params = []) => Route::has($name)
        ? route($name, $params)
        : '#';

    $courses = $academicYear->courses ?? collect();
    $coursesCount = (int) ($academicYear->courses_count ?? $courses->count());

    $backUrl = Route::has('admin.tahun-akademik.index')
        ? route('admin.tahun-akademik.index')
        : (Route::has('admin.dashboard') ? route('admin.dashboard') : '#');

    $canDelete = $coursesCount === 0;
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Detail Tahun Akademik</h1>

    <p>
        Detail periode tahun akademik, status aktif, dan daftar mata kuliah
        yang memakai periode tahun akademik ini.
    </p>

    <div class="hero-actions">
        <a href="{{ $backUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('admin.tahun-akademik.edit'))
            <a href="{{ route('admin.tahun-akademik.edit', $academicYear) }}" class="btn btn-primary">
                Edit Tahun Akademik
            </a>
        @endif
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">
                Tahun Akademik {{ $academicYear->year }}
            </h2>

            <div class="section-subtitle">
                Ringkasan periode, status, dan jumlah mata kuliah yang terhubung.
            </div>
        </div>

        <span class="status-pill {{ $academicYear->is_active ? 'status-success' : 'status-danger' }}">
            {{ $academicYear->is_active ? 'Aktif' : 'Nonaktif' }}
        </span>
    </div>

    <div class="grid grid-4">
        <div class="stat-card">
            <div class="stat-label">Tahun</div>
            <div class="stat-value" style="font-size: 24px;">
                {{ $academicYear->year }}
            </div>
            <div class="stat-note">
                Periode tahun akademik.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Periode</div>
            <div class="stat-value" style="font-size: 24px;">
                {{ ucfirst($academicYear->semester) }}
            </div>
            <div class="stat-note">
                Ganjil atau genap.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Status</div>
            <div class="stat-value" style="font-size: 24px;">
                {{ $academicYear->is_active ? 'Aktif' : 'Nonaktif' }}
            </div>
            <div class="stat-note">
                Status penggunaan tahun akademik.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Mata Kuliah</div>
            <div class="stat-value">
                {{ $coursesCount }}
            </div>
            <div class="stat-note">
                Total mata kuliah pada periode ini.
            </div>
        </div>
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Mata Kuliah pada Tahun Akademik Ini</h2>
            <div class="section-subtitle">
                Daftar mata kuliah, semester mahasiswa, SKS, kelas praktikum, dan status mata kuliah.
            </div>
        </div>

        @if(Route::has('admin.matakuliah.create'))
            <a href="{{ route('admin.matakuliah.create') }}" class="btn btn-primary btn-sm">
                + Tambah Mata Kuliah
            </a>
        @endif
    </div>

    @if($courses->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">📚</div>

            <h3 class="empty-state-title">
                Belum ada mata kuliah
            </h3>

            <p class="empty-state-text">
                Belum ada mata kuliah yang memakai tahun akademik ini.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Mata Kuliah</th>
                            <th>Kode</th>
                            <th>Semester Mahasiswa</th>
                            <th>SKS</th>
                            <th>Kelas Praktikum</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($courses as $course)
                            <tr>
                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <a href="{{ $safe('admin.matakuliah.show', [$course]) }}">
                                            <strong>{{ $course->name }}</strong>
                                        </a>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ $course->description ?? 'Tidak ada deskripsi.' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="status-pill status-muted">
                                        {{ $course->code ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $course->studySemester?->name ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill status-info">
                                        {{ $course->sks ?? '-' }} SKS
                                    </span>
                                </td>

                                <td>
                                    @forelse($course->classes as $class)
                                        <div style="margin-bottom: 12px;">
                                            <a href="{{ $safe('admin.kelas.show', [$class]) }}">
                                                <strong>{{ $class->name }}</strong>
                                            </a>

                                            <div class="item-meta" style="margin-top: 3px;">
                                                Asisten: {{ $class->assistant?->name ?? '-' }}
                                                <br>
                                                Ruang: {{ $class->room ?? '-' }}
                                                · Jadwal: {{ $class->schedule ?? '-' }}
                                            </div>
                                        </div>
                                    @empty
                                        <span class="item-meta" style="margin-top: 0;">
                                            Belum ada kelas praktikum.
                                        </span>
                                    @endforelse
                                </td>

                                <td>
                                    <span class="status-pill {{ $course->is_active ? 'status-success' : 'status-danger' }}">
                                        {{ $course->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
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
            <h2 class="section-title">Aksi Tahun Akademik</h2>
            <div class="section-subtitle">
                Kembali ke daftar tahun akademik, edit data, atau hapus jika belum dipakai mata kuliah.
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a class="btn" href="{{ $backUrl }}">
            Kembali
        </a>

        @if(Route::has('admin.tahun-akademik.edit'))
            <a class="btn btn-primary" href="{{ route('admin.tahun-akademik.edit', $academicYear) }}">
                Edit Tahun Akademik
            </a>
        @endif

        @if($canDelete && Route::has('admin.tahun-akademik.destroy'))
            @include('partials.delete-button', [
                'action' => route('admin.tahun-akademik.destroy', $academicYear),
                'confirm' => 'Yakin ingin menghapus tahun akademik ini?'
            ])
        @endif
    </div>
</section>
@endsection