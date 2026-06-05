@extends('layouts.app')

@section('title', 'Laporan Nilai')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $statistics = $statistics ?? [];
    $studySemesters = $studySemesters ?? collect();
    $courses = $courses ?? collect();
    $classes = $classes ?? collect();
    $submissions = $submissions ?? collect();

    $dashboardUrl = Route::has('admin.dashboard')
        ? route('admin.dashboard')
        : '#';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Laporan Admin</div>

    <h1>Laporan Nilai</h1>

    <p>
        Pantau nilai submission mahasiswa berdasarkan semester, mata kuliah,
        kelas praktikum, rentang tanggal, dan status penilaian.
    </p>

    <div class="hero-actions">
        <a href="{{ $dashboardUrl }}" class="btn">
            ← Dashboard
        </a>

        <a
            href="{{ route('admin.reports.scores.export', request()->query()) }}"
            class="btn btn-primary"
        >
            Export CSV
        </a>
    </div>
</section>

<div class="grid grid-4" style="margin-bottom: 18px;">
    <div class="stat-card">
        <div class="stat-label">Total Submission</div>
        <div class="stat-value">
            {{ $statistics['total_submissions'] ?? 0 }}
        </div>
        <div class="stat-note">
            Total pengumpulan tugas mahasiswa.
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Sudah Dinilai</div>
        <div class="stat-value">
            {{ $statistics['graded_submissions'] ?? 0 }}
        </div>
        <div class="stat-note">
            Submission yang sudah diberi nilai.
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Belum Dinilai</div>
        <div class="stat-value">
            {{ $statistics['ungraded_submissions'] ?? 0 }}
        </div>
        <div class="stat-note">
            Submission yang masih perlu diperiksa.
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Rata-rata Nilai</div>
        <div class="stat-value">
            {{ $statistics['average_score'] ?? 0 }}
        </div>
        <div class="stat-note">
            Rata-rata nilai dari submission yang dinilai.
        </div>
    </div>
</div>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Filter Laporan Nilai</h2>
            <div class="section-subtitle">
                Gunakan filter untuk melihat laporan berdasarkan mahasiswa, tugas, semester, mata kuliah, kelas, status nilai, atau tanggal submission.
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
            placeholder="Cari mahasiswa/tugas"
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

        <select class="form-control" style="width: 170px;" name="grading_status">
            <option value="">Semua status</option>

            <option value="graded" @selected(request('grading_status') === 'graded')>
                Sudah dinilai
            </option>

            <option value="ungraded" @selected(request('grading_status') === 'ungraded')>
                Belum dinilai
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

        @if(request()->hasAny(['search', 'study_semester_id', 'course_id', 'class_id', 'grading_status', 'date_from', 'date_to']))
            <a href="{{ route('admin.reports.scores') }}" class="btn">
                Reset
            </a>
        @endif
    </form>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Data Nilai Submission</h2>
            <div class="section-subtitle">
                Daftar submission mahasiswa beserta nilai, status penilaian, feedback, dan waktu pengumpulan.
            </div>
        </div>

        <a
            href="{{ route('admin.reports.scores.export', request()->query()) }}"
            class="btn btn-primary btn-sm"
        >
            Export CSV
        </a>
    </div>

    @if($submissions->count() === 0)
        <div class="empty-state">
            <h3 class="empty-state-title">
                Belum ada data nilai
            </h3>

            <p class="empty-state-text">
                Belum ada data nilai sesuai filter yang dipilih.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Semester</th>
                            <th>Mata Kuliah / Kelas</th>
                            <th>Tugas</th>
                            <th>Nilai</th>
                            <th>Status</th>
                            <th>Feedback</th>
                            <th>Dikumpulkan</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($submissions as $submission)
                            <tr>
                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $submission->student?->name ?? '-' }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ $submission->student?->nim_nip ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $submission->student?->studySemester?->name ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $submission->assignment?->kelas?->course?->code ?? '-' }}
                                            -
                                            {{ $submission->assignment?->kelas?->course?->name ?? '-' }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ $submission->assignment?->kelas?->name ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $submission->assignment?->title ?? '-' }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            Maks: {{ $submission->assignment?->max_score ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    @if($submission->score !== null)
                                        <span class="status-pill status-info">
                                            {{ $submission->score }}
                                        </span>
                                    @else
                                        <span class="status-pill status-muted">
                                            -
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if($submission->graded_at)
                                        <span class="status-pill status-success">
                                            Sudah dinilai
                                        </span>
                                    @else
                                        <span class="status-pill status-danger">
                                            Belum dinilai
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $submission->feedback ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $submission->submitted_at?->format('d M Y H:i') ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 18px;">
            {{ $submissions->links() }}
        </div>
    @endif
</section>
@endsection