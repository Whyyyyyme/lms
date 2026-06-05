@extends('layouts.app')

@section('title', 'Nilai Saya')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $averageScore = $averageScore ?? 0;
    $submissions = $submissions ?? collect();

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
        Nilai akan muncul setelah submission kamu diperiksa dan dinilai.
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
    </div>
</section>

<div class="grid grid-3" style="margin-bottom: 18px;">
    <div class="stat-card">
        <div class="stat-label">Rata-rata Nilai</div>
        <div class="stat-value">
            {{ number_format((float) $averageScore, 2) }}
        </div>
        <div class="stat-note">
            Rata-rata nilai dari submission yang sudah dinilai.
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Total Submission</div>
        <div class="stat-value">
            {{ method_exists($submissions, 'total') ? $submissions->total() : $submissions->count() }}
        </div>
        <div class="stat-note">
            Total submission tugas yang tercatat.
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Status</div>
        <div class="stat-value" style="font-size: 22px;">
            Nilai Praktikum
        </div>
        <div class="stat-note">
            Feedback ditampilkan jika sudah diberikan oleh asisten.
        </div>
    </div>
</div>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Nilai Tugas</h2>
            <div class="section-subtitle">
                Nilai ditampilkan berdasarkan submission tugas yang sudah kamu kumpulkan.
            </div>
        </div>
    </div>

    @if($submissions->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">⭐</div>

            <h3 class="empty-state-title">
                Belum ada nilai
            </h3>

            <p class="empty-state-text">
                Nilai akan muncul setelah kamu mengumpulkan tugas dan asisten melakukan penilaian.
            </p>
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
                            @endphp

                            <tr>
                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $submission->assignment?->title ?? 'Tugas tidak ditemukan' }}
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
                                            {{ $submission->assignment?->kelas?->course?->name ?? 'Mata kuliah tidak ditemukan' }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ $submission->assignment?->kelas?->name ?? 'Kelas tidak ditemukan' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    @if($isGraded)
                                        <span class="status-pill status-success">
                                            {{ $submission->score }}
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