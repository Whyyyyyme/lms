@extends('layouts.app')

@section('title', 'Nilai Saya')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $averageScore = $averageScore ?? null;
    $submissions = $submissions ?? collect();
    $totalSubmissions = $totalSubmissions ?? (method_exists($submissions, 'total') ? $submissions->total() : $submissions->count());
    $gradedSubmissions = $gradedSubmissions ?? 0;
    $archivedClassesCount = $archivedClassesCount ?? 0;

    $dashboardUrl = Route::has('student.dashboard')
        ? route('student.dashboard')
        : '#';

    $coursesUrl = Route::has('student.courses.index')
        ? route('student.courses.index')
        : '#';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Mahasiswa</div>

    <h1>Nilai Saya</h1>

    <p>
        Pantau nilai tugas praktikum dan feedback dari asisten.
        Halaman ini menampilkan nilai dari mata kuliah yang masih aktif.
    </p>

    <div class="hero-actions">
        <a href="{{ $dashboardUrl }}" class="btn">
            ← Dashboard
        </a>

        @if(Route::has('student.courses.index'))
            <a href="{{ $coursesUrl }}" class="btn btn-primary">
                📚 Mata Kuliah Saya
            </a>
        @endif

        @if(Route::has('student.grades.history'))
            <a href="{{ route('student.grades.history') }}" class="btn">
                🕘 Riwayat Nilai{{ $archivedClassesCount > 0 ? ' ('.$archivedClassesCount.')' : '' }}
            </a>
        @endif
    </div>
</section>

<div class="grid grid-3" style="margin-bottom: 18px;">
    <div class="stat-card">
        <div class="stat-label">Rata-rata Nilai Aktif</div>
        <div class="stat-value">
            {{ $averageScore !== null ? number_format((float) $averageScore, 2) : '-' }}
        </div>
        <div class="stat-note">
            Rata-rata nilai dari submission kelas aktif yang sudah dinilai.
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Total Submission</div>
        <div class="stat-value">
            {{ $totalSubmissions }}
        </div>
        <div class="stat-note">
            Total submission tugas pada mata kuliah aktif.
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Sudah Dinilai</div>
        <div class="stat-value">
            {{ $gradedSubmissions }}
        </div>
        <div class="stat-note">
            Submission yang sudah memiliki skor dari asisten.
        </div>
    </div>
</div>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Nilai Tugas Aktif</h2>
            <div class="section-subtitle">
                Nilai ditampilkan berdasarkan submission tugas dari kelas praktikum aktif yang dapat kamu akses.
            </div>
        </div>
    </div>

    @if($submissions->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">⭐</div>

            <h3 class="empty-state-title">
                Belum ada nilai aktif
            </h3>

            <p class="empty-state-text">
                Nilai aktif akan muncul setelah kamu mengumpulkan tugas pada mata kuliah aktif dan asisten melakukan penilaian.
            </p>

            @if($archivedClassesCount > 0 && Route::has('student.grades.history'))
                <div style="margin-top: 14px;">
                    <a href="{{ route('student.grades.history') }}" class="btn btn-primary">
                        Lihat Riwayat Nilai
                    </a>
                </div>
            @endif
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Tugas</th>
                            <th>Kelas</th>
                            <th>Nilai</th>
                            <th>Feedback</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($submissions as $submission)
                            @php
                                $isGraded = $submission->score !== null;
                                $assignment = $submission->assignment;
                                $class = $assignment?->kelas;
                                $course = $class?->course;
                            @endphp

                            <tr>
                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $assignment?->title ?? 'Tugas tidak ditemukan' }}
                                        </strong>

                                        @if($submission->submitted_at)
                                            <span class="item-meta" style="margin-top: 0;">
                                                Dikumpulkan:
                                                {{ $submission->submitted_at->format('d M Y H:i') }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $course?->name ?? 'Mata kuliah tidak ditemukan' }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ $class?->name ?? 'Kelas tidak ditemukan' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    @if($isGraded)
                                        <span class="status-pill status-success">
                                            {{ number_format((float) $submission->score, 2) }}
                                        </span>
                                    @else
                                        <span class="status-pill status-warning">
                                            Belum dinilai
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $submission->feedback ?: '-' }}
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
