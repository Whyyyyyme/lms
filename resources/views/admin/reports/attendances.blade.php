@extends('layouts.app')

@section('title', 'Laporan Absensi')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $statistics = $statistics ?? [];
    $studySemesters = $studySemesters ?? collect();
    $courses = $courses ?? collect();
    $classes = $classes ?? collect();
    $attendances = $attendances ?? collect();

    $dashboardUrl = Route::has('admin.dashboard')
        ? route('admin.dashboard')
        : '#';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Laporan Admin</div>

    <h1>Laporan Absensi</h1>

    <p>
        Pantau sesi absensi dan rekap kehadiran mahasiswa berdasarkan semester,
        mata kuliah, kelas praktikum, status sesi, status kehadiran, dan rentang tanggal.
    </p>

    <div class="hero-actions">
        <a href="{{ $dashboardUrl }}" class="btn">
            ← Dashboard
        </a>

        <a
            href="{{ route('admin.reports.attendances.export', request()->query()) }}"
            class="btn btn-primary"
        >
            Export CSV
        </a>
    </div>
</section>

<div class="grid grid-4" style="margin-bottom: 18px;">
    <div class="stat-card">
        <div class="stat-label">Total Sesi</div>
        <div class="stat-value">
            {{ $statistics['total_sessions'] ?? 0 }}
        </div>
        <div class="stat-note">
            Total sesi absensi yang tercatat.
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Sesi Terbuka</div>
        <div class="stat-value">
            {{ $statistics['open_sessions'] ?? 0 }}
        </div>
        <div class="stat-note">
            Sesi absensi yang sedang dibuka.
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Sesi Ditutup</div>
        <div class="stat-value">
            {{ $statistics['closed_sessions'] ?? 0 }}
        </div>
        <div class="stat-note">
            Sesi absensi yang sudah ditutup.
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Total Record</div>
        <div class="stat-value">
            {{ $statistics['total_records'] ?? 0 }}
        </div>
        <div class="stat-note">
            Total data kehadiran mahasiswa.
        </div>
    </div>
</div>

<div class="grid grid-3" style="margin-bottom: 18px;">
    <div class="stat-card">
        <div class="stat-label">Hadir</div>
        <div class="stat-value">
            {{ $statistics['hadir_records'] ?? 0 }}
        </div>
        <div class="stat-note">
            Total mahasiswa dengan status hadir.
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Izin</div>
        <div class="stat-value">
            {{ $statistics['izin_records'] ?? 0 }}
        </div>
        <div class="stat-note">
            Total mahasiswa dengan status izin.
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Alpha</div>
        <div class="stat-value">
            {{ $statistics['alpha_records'] ?? 0 }}
        </div>
        <div class="stat-note">
            Total mahasiswa dengan status alpha.
        </div>
    </div>
</div>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Filter Laporan Absensi</h2>
            <div class="section-subtitle">
                Gunakan filter untuk melihat absensi berdasarkan kelas, mata kuliah, semester, status sesi, status kehadiran, atau rentang tanggal.
            </div>
        </div>
    </div>

    <form method="GET" class="actions-inline">
        <input
            class="form-control"
            style="width: 220px;"
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari kelas/matkul/asisten"
        >

        <select class="form-control" style="width: 180px;" name="study_semester_id">
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

        <select class="form-control" style="width: 220px;" name="course_id">
            <option value="">Semua mata kuliah</option>

            @foreach($courses as $course)
                <option
                    value="{{ $course->id }}"
                    @selected((string) request('course_id') === (string) $course->id)
                >
                    {{ $course->code }} - {{ $course->name }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width: 200px;" name="class_id">
            <option value="">Semua kelas</option>

            @foreach($classes as $class)
                <option
                    value="{{ $class->id }}"
                    @selected((string) request('class_id') === (string) $class->id)
                >
                    {{ $class->course?->code ?? '-' }} - {{ $class->name }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width: 160px;" name="session_status">
            <option value="">Semua sesi</option>

            <option value="open" @selected(request('session_status') === 'open')>
                Terbuka
            </option>

            <option value="closed" @selected(request('session_status') === 'closed')>
                Ditutup
            </option>
        </select>

        <select class="form-control" style="width: 160px;" name="record_status">
            <option value="">Semua hadir</option>

            <option value="hadir" @selected(request('record_status') === 'hadir')>
                Hadir
            </option>

            <option value="izin" @selected(request('record_status') === 'izin')>
                Izin
            </option>

            <option value="alpha" @selected(request('record_status') === 'alpha')>
                Alpha
            </option>
        </select>

        <input
            class="form-control"
            style="width: 160px;"
            type="date"
            name="date_from"
            value="{{ request('date_from') }}"
        >

        <input
            class="form-control"
            style="width: 160px;"
            type="date"
            name="date_to"
            value="{{ request('date_to') }}"
        >

        <button class="btn" type="submit">
            Filter
        </button>

        @if(request()->hasAny(['search', 'study_semester_id', 'course_id', 'class_id', 'session_status', 'record_status', 'date_from', 'date_to']))
            <a href="{{ route('admin.reports.attendances') }}" class="btn">
                Reset
            </a>
        @endif
    </form>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Data Absensi</h2>
            <div class="section-subtitle">
                Daftar sesi absensi beserta kelas, pembuka sesi, jumlah record, rekap kehadiran, dan status sesi.
            </div>
        </div>

        <a
            href="{{ route('admin.reports.attendances.export', request()->query()) }}"
            class="btn btn-primary btn-sm"
        >
            Export CSV
        </a>
    </div>

    @if($attendances->count() === 0)
        <div class="empty-state">
            <h3 class="empty-state-title">
                Belum ada data absensi
            </h3>

            <p class="empty-state-text">
                Belum ada data absensi sesuai filter yang dipilih.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Semester</th>
                            <th>Mata Kuliah / Kelas</th>
                            <th>Dibuka Oleh</th>
                            <th>Record</th>
                            <th>Rekap</th>
                            <th>Status Sesi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($attendances as $attendance)
                            <tr>
                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $attendance->session_date?->format('d M Y') ?? '-' }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            Buka:
                                            {{ $attendance->opened_at?->format('H:i') ?? '-' }}
                                            |
                                            Tutup:
                                            {{ $attendance->closed_at?->format('H:i') ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $attendance->kelas?->course?->studySemester?->name ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $attendance->kelas?->course?->code ?? '-' }}
                                            -
                                            {{ $attendance->kelas?->course?->name ?? '-' }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ $attendance->kelas?->name ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $attendance->opener?->name ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill status-muted">
                                        {{ $attendance->records_count }} Record
                                    </span>
                                </td>

                                <td>
                                    <div style="display: grid; gap: 6px;">
                                        <span class="status-pill status-success">
                                            Hadir: {{ $attendance->hadir_count ?? 0 }}
                                        </span>

                                        <span class="status-pill status-warning">
                                            Izin: {{ $attendance->izin_count ?? 0 }}
                                        </span>

                                        <span class="status-pill status-danger">
                                            Alpha: {{ $attendance->alpha_count ?? 0 }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="status-pill {{ $attendance->is_open ? 'status-success' : 'status-danger' }}">
                                        {{ $attendance->is_open ? 'Terbuka' : 'Ditutup' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 18px;">
            {{ $attendances->links() }}
        </div>
    @endif
</section>
@endsection