@extends('layouts.app')

@section('title', 'Kelola Mata Kuliah')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $courses = $courses ?? collect();
    $studySemesters = $studySemesters ?? collect();
    $academicYears = $academicYears ?? collect();

    $dashboardUrl = Route::has('admin.dashboard')
        ? route('admin.dashboard')
        : '#';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Kelola Mata Kuliah</h1>

    <p>
        Kelola data mata kuliah praktikum berdasarkan semester mahasiswa,
        tahun akademik, SKS, kelas praktikum, dan status aktif mata kuliah.
    </p>

    <div class="hero-actions">
        <a href="{{ $dashboardUrl }}" class="btn">
            ← Dashboard
        </a>

        <a href="{{ route('admin.matakuliah.create') }}" class="btn btn-primary">
            + Tambah Mata Kuliah
        </a>
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Filter Mata Kuliah</h2>
            <div class="section-subtitle">
                Cari mata kuliah berdasarkan nama, kode, semester mahasiswa, tahun akademik, atau status.
            </div>
        </div>
    </div>

    <form method="GET" class="actions-inline">
        <input
            class="form-control"
            style="width: 240px;"
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari nama/kode"
        >

        <select class="form-control" style="width: 190px;" name="study_semester_id">
            <option value="">Semua semester</option>

            @foreach($studySemesters as $semester)
                <option
                    value="{{ $semester->id }}"
                    @selected((string) request('study_semester_id') === (string) $semester->id)
                >
                    {{ $semester->name }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width: 220px;" name="academic_year_id">
            <option value="">Semua tahun akademik</option>

            @foreach($academicYears as $year)
                <option
                    value="{{ $year->id }}"
                    @selected((string) request('academic_year_id') === (string) $year->id)
                >
                    {{ $year->year }} - {{ ucfirst($year->semester) }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width: 150px;" name="status">
            <option value="">Semua status</option>

            <option value="1" @selected(request('status') === '1')>
                Aktif
            </option>

            <option value="0" @selected(request('status') === '0')>
                Nonaktif
            </option>
        </select>

        <button class="btn" type="submit">
            Filter
        </button>

        @if(request()->hasAny(['search', 'study_semester_id', 'academic_year_id', 'status']))
            <a href="{{ route('admin.matakuliah.index') }}" class="btn">
                Reset
            </a>
        @endif
    </form>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Mata Kuliah</h2>
            <div class="section-subtitle">
                Mata kuliah menjadi dasar pembuatan kelas praktikum, materi, tugas, dan absensi.
            </div>
        </div>

        <a href="{{ route('admin.matakuliah.create') }}" class="btn btn-primary btn-sm">
            + Tambah Mata Kuliah
        </a>
    </div>

    @if($courses->count() === 0)
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">📚</div>

            <h3 class="empty-state-title">
                Belum ada mata kuliah
            </h3>

            <p class="empty-state-text">
                Data mata kuliah akan tampil setelah admin menambahkan mata kuliah praktikum.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Mata Kuliah</th>
                            <th>Semester Mahasiswa</th>
                            <th>Tahun Akademik</th>
                            <th>SKS</th>
                            <th>Kelas</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($courses as $course)
                            <tr>
                                <td>
                                    <span class="status-pill status-muted">
                                        {{ $course->code ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $course->name }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ $course->description ?? 'Tidak ada deskripsi.' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $course->studySemester?->name ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $course->academicYear?->year ?? '-' }}
                                        </strong>

                                        @if($course->academicYear)
                                            <span class="status-pill {{ $course->academicYear->semester === 'ganjil' ? 'status-warning' : 'status-info' }}">
                                                {{ ucfirst($course->academicYear->semester) }}
                                            </span>
                                        @else
                                            <span class="item-meta" style="margin-top: 0;">
                                                Tahun akademik belum diatur
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <span class="status-pill status-info">
                                        {{ $course->sks ?? '-' }} SKS
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill status-muted">
                                        {{ $course->classes_count ?? 0 }} Kelas
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill {{ $course->is_active ? 'status-success' : 'status-danger' }}">
                                        {{ $course->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="actions-inline">
                                        <a class="btn btn-sm" href="{{ route('admin.matakuliah.show', $course) }}">
                                            Detail
                                        </a>

                                        <a class="btn btn-sm" href="{{ route('admin.matakuliah.edit', $course) }}">
                                            Edit
                                        </a>

                                        @include('partials.delete-button', [
                                            'action' => route('admin.matakuliah.destroy', $course),
                                            'confirm' => 'Yakin ingin menghapus mata kuliah ini?'
                                        ])
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 18px;">
            {{ $courses->links() }}
        </div>
    @endif
</section>
@endsection