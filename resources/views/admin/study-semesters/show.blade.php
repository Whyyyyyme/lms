@extends('layouts.app')

@section('title', $studySemester->name)

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $safe = fn (string $name, array $params = []) => Route::has($name)
        ? route($name, $params)
        : '#';

    $courses = $studySemester->courses ?? collect();
    $students = $studySemester->students ?? collect();

    $coursesCount = (int) ($studySemester->courses_count ?? $courses->count());
    $studentsCount = (int) ($studySemester->students_count ?? $students->count());
    $enrollmentsCount = (int) ($studySemester->enrollments_count ?? 0);

    $canDelete = $coursesCount === 0
        && $studentsCount === 0
        && $enrollmentsCount === 0;

    $backUrl = Route::has('admin.semester.index')
        ? route('admin.semester.index')
        : (Route::has('admin.dashboard') ? route('admin.dashboard') : '#');
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>{{ $studySemester->name }}</h1>

    <p>
        Detail semester mahasiswa, status aktif, mata kuliah, kelas praktikum,
        dan mahasiswa yang terhubung dengan semester ini.
    </p>

    <div class="hero-actions">
        <a href="{{ $backUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('admin.semester.edit'))
            <a href="{{ route('admin.semester.edit', $studySemester) }}" class="btn btn-primary">
                Edit Semester
            </a>
        @endif
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Informasi Semester</h2>
            <div class="section-subtitle">
                Ringkasan level semester, status, jumlah mata kuliah, mahasiswa, dan riwayat enrollment.
            </div>
        </div>

        <span class="status-pill {{ $studySemester->is_active ? 'status-success' : 'status-danger' }}">
            {{ $studySemester->is_active ? 'Aktif' : 'Nonaktif' }}
        </span>
    </div>

    <div class="grid grid-4">
        <div class="stat-card">
            <div class="stat-label">Level</div>
            <div class="stat-value">
                {{ $studySemester->level }}
            </div>
            <div class="stat-note">
                Level semester mahasiswa.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Mata Kuliah</div>
            <div class="stat-value">
                {{ $coursesCount }}
            </div>
            <div class="stat-note">
                Total mata kuliah pada semester ini.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Mahasiswa</div>
            <div class="stat-value">
                {{ $studentsCount }}
            </div>
            <div class="stat-note">
                Total mahasiswa yang memakai semester ini.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Riwayat Enrollment</div>
            <div class="stat-value">
                {{ $enrollmentsCount }}
            </div>
            <div class="stat-note">
                Riwayat akses/enrollment pada semester ini.
            </div>
        </div>
    </div>

    <div style="margin-top: 18px;">
        <div class="stat-card">
            <div class="stat-label">Deskripsi</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $studySemester->description ?: '-' }}
            </div>
            <div class="stat-note">
                Keterangan tambahan untuk semester mahasiswa.
            </div>
        </div>
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Mata Kuliah & Kelas Praktikum</h2>
            <div class="section-subtitle">
                Daftar mata kuliah yang terhubung dengan semester ini beserta kelas praktikumnya.
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
                Belum ada mata kuliah untuk semester ini.
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
                            <th>SKS</th>
                            <th>Tahun Akademik</th>
                            <th>Kelas Praktikum</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($courses as $course)
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
                                    <span class="status-pill status-info">
                                        {{ $course->sks ?? '-' }} SKS
                                    </span>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $course->academicYear?->name ?? '-' }}
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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Mahasiswa Semester Ini</h2>
            <div class="section-subtitle">
                Daftar mahasiswa yang memiliki semester ini pada data akunnya.
            </div>
        </div>

        @if(Route::has('admin.users.create'))
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
                + Tambah User
            </a>
        @endif
    </div>

    @if($students->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">🎓</div>

            <h3 class="empty-state-title">
                Belum ada mahasiswa
            </h3>

            <p class="empty-state-text">
                Belum ada mahasiswa yang terhubung dengan semester ini.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>NIM</th>
                            <th>Email</th>
                            <th>Rombel</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($students as $student)
                            <tr>
                                <td>
                                    <strong>{{ $student->name }}</strong>
                                </td>

                                <td>
                                    {{ $student->nim_nip ?? '-' }}
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $student->email }}
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill status-muted">
                                        {{ $student->student_group ? 'Kelas '.strtoupper($student->student_group) : '-' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill {{ $student->is_active ? 'status-success' : 'status-danger' }}">
                                        {{ $student->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>

                                <td>
                                    <a class="btn btn-sm" href="{{ $safe('admin.users.show', [$student]) }}">
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
            <h2 class="section-title">Aksi Semester</h2>
            <div class="section-subtitle">
                Kembali ke daftar semester, edit data semester, atau hapus semester jika belum memiliki relasi data.
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a class="btn" href="{{ $backUrl }}">
            Kembali
        </a>

        @if(Route::has('admin.semester.edit'))
            <a class="btn btn-primary" href="{{ route('admin.semester.edit', $studySemester) }}">
                Edit Semester
            </a>
        @endif

        @if($canDelete && Route::has('admin.semester.destroy'))
            @include('partials.delete-button', [
                'action' => route('admin.semester.destroy', $studySemester),
                'confirm' => 'Yakin ingin menghapus semester ini?'
            ])
        @endif
    </div>
</section>
@endsection