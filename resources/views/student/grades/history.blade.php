@extends('layouts.app')

@section('title', 'Riwayat Nilai')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $averageScore = $averageScore ?? null;
    $submissions = $submissions ?? collect();
    $totalSubmissions = $totalSubmissions ?? (method_exists($submissions, 'total') ? $submissions->total() : $submissions->count());
    $gradedSubmissions = $gradedSubmissions ?? 0;
    $archivedClassesCount = $archivedClassesCount ?? 0;
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Mahasiswa</div>

    <h1>Riwayat Nilai</h1>

    <p>
        Halaman ini menampilkan nilai dan feedback lama dari kelas yang tahun akademik atau semesternya sudah selesai.
        Riwayat nilai hanya untuk dibaca sebagai arsip pembelajaran.
    </p>

    <div class="hero-actions">
        <a href="{{ route('student.grades.index') }}" class="btn">
            ← Nilai Aktif
        </a>

        @if(Route::has('student.courses.history'))
            <a href="{{ route('student.courses.history') }}" class="btn btn-primary">
                🕘 Riwayat Mata Kuliah
            </a>
        @endif

        @if(Route::has('student.assignments.history'))
            <a href="{{ route('student.assignments.history') }}" class="btn">
                📝 Riwayat Tugas
            </a>
        @endif
    </div>
</section>

<div class="alert" style="margin-bottom: 18px;">
    Riwayat nilai berasal dari submission pada mata kuliah yang sudah selesai. Kamu tetap bisa melihat skor dan feedback,
    tetapi pengumpulan atau perubahan submission dilakukan dari halaman tugas aktif saja.
</div>

<div class="grid grid-3" style="margin-bottom: 18px;">
    <div class="stat-card">
        <div class="stat-label">Rata-rata Nilai Riwayat</div>
        <div class="stat-value">
            {{ $averageScore !== null ? number_format((float) $averageScore, 2) : '-' }}
        </div>
        <div class="stat-note">
            Rata-rata nilai dari submission riwayat yang sudah dinilai.
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Total Submission Lama</div>
        <div class="stat-value">
            {{ $totalSubmissions }}
        </div>
        <div class="stat-note">
            Submission dari kelas yang sudah menjadi riwayat.
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Kelas Riwayat</div>
        <div class="stat-value">
            {{ $archivedClassesCount }}
        </div>
        <div class="stat-note">
            Kelas dari tahun akademik nonaktif yang pernah kamu ikuti.
        </div>
    </div>
</div>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Riwayat Nilai Tugas</h2>
            <div class="section-subtitle">
                Tampilan mengikuti tema nilai aktif, tetapi data dibatasi hanya dari kelas riwayat.
            </div>
        </div>
    </div>

    @if($submissions->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">⭐</div>

            <h3 class="empty-state-title">
                Belum ada riwayat nilai
            </h3>

            <p class="empty-state-text">
                Riwayat nilai akan muncul setelah ada submission pada kelas yang sudah selesai dan tahun akademiknya dinonaktifkan oleh admin.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Tugas</th>
                            <th>Mata Kuliah / Kelas</th>
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
                                $academicYear = $course?->academicYear;
                            @endphp

                            <tr>
                                <td>
                                    <div style="display: grid; gap: 6px;">
                                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                            <span class="course-code">
                                                {{ $course?->code ?? 'Nilai' }}
                                            </span>

                                            <span class="status-pill status-muted">
                                                Riwayat
                                            </span>
                                        </div>

                                        <strong>
                                            {{ $assignment?->title ?? 'Tugas tidak ditemukan' }}
                                        </strong>

                                        @if($submission->submitted_at)
                                            <span class="item-meta" style="margin-top: 0;">
                                                Dikumpulkan:
                                                {{ $submission->submitted_at->format('d M Y H:i') }}
                                            </span>
                                        @endif

                                        @if($submission->graded_at)
                                            <span class="item-meta" style="margin-top: 0;">
                                                Dinilai:
                                                {{ $submission->graded_at->format('d M Y H:i') }}
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

                                            @if($academicYear)
                                                <br>
                                                Tahun Akademik {{ $academicYear->name }}
                                            @endif
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
