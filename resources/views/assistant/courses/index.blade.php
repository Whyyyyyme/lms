@extends('layouts.app')

@section('title', 'Mata Kuliah Diajar')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $classes = $classes ?? collect();
    $statistics = $statistics ?? [];

    $dashboardUrl = Route::has('assistant.dashboard')
        ? route('assistant.dashboard')
        : '#';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Asisten Praktikum</div>

    <h1>Mata Kuliah yang Diajar</h1>

    <p>
        Pilih mata kuliah atau kelas praktikum terlebih dahulu.
        Setelah masuk ke kelas, kamu bisa mengelola materi, tugas, absensi, dan submission khusus untuk kelas tersebut.
    </p>

    <div class="hero-actions">
        <a href="{{ $dashboardUrl }}" class="btn">
            ← Dashboard
        </a>
    </div>
</section>

<div class="grid grid-4" style="margin-bottom: 18px;">
    <div class="stat-card">
        <div class="stat-label">Kelas Diampu</div>
        <div class="stat-value">{{ $statistics['total_kelas'] ?? 0 }}</div>
        <div class="stat-note">Total kelas praktikum yang kamu kelola.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Mahasiswa</div>
        <div class="stat-value">{{ $statistics['total_mahasiswa'] ?? 0 }}</div>
        <div class="stat-note">Mahasiswa yang terdaftar di kelas kamu.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Materi</div>
        <div class="stat-value">{{ $statistics['total_materi'] ?? 0 }}</div>
        <div class="stat-note">Materi yang sudah dibuat.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Tugas</div>
        <div class="stat-value">{{ $statistics['total_tugas'] ?? 0 }}</div>
        <div class="stat-note">Tugas praktikum yang sudah dibuat.</div>
    </div>
</div>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Kelas Praktikum</h2>
            <div class="section-subtitle">
                Masuk ke salah satu kelas untuk mengelola materi, tugas, absensi, dan submission.
            </div>
        </div>
    </div>

    @if($classes->isEmpty())
        <div class="alert alert-error">
            Kamu belum ditugaskan ke kelas/mata kuliah mana pun.
            Minta admin mengatur asisten pada menu kelas praktikum.
        </div>
    @else
        <div class="course-grid">
            @foreach($classes as $class)
                @php
                    $course = $class->course;
                    $semester = $course?->studySemester;
                    $academicYear = $course?->academicYear;
                @endphp

                <a href="{{ route('assistant.courses.show', $class) }}" class="course-card">
                    <div>
                        <span class="course-code">
                            {{ $course?->code ?? 'Kode MK' }}
                        </span>

                        <h3 class="course-title">
                            {{ $course?->name ?? 'Mata kuliah tidak tersedia' }}
                        </h3>

                        <div class="course-meta">
                            {{ $class->name }}

                            @if($semester)
                                · {{ $semester->name }}
                            @endif

                            @if($academicYear)
                                <br>
                                Tahun Akademik {{ $academicYear->name }}
                            @endif

                            @if($class->room)
                                <br>
                                Ruang {{ $class->room }}
                            @endif

                            @if($class->schedule)
                                <br>
                                Jadwal {{ $class->schedule }}
                            @endif
                        </div>

                        <div class="metric-row">
                            <span class="metric-pill">
                                📘 {{ $class->materials_count ?? 0 }} Materi
                            </span>

                            <span class="metric-pill">
                                📝 {{ $class->assignments_count ?? 0 }} Tugas
                            </span>

                            <span class="metric-pill">
                                ✅ {{ $class->attendances_count ?? 0 }} Absensi
                            </span>

                            <span class="metric-pill">
                                🎓 {{ $class->resolved_students_count ?? 0 }} Mahasiswa
                            </span>
                        </div>
                    </div>

                    <div class="course-footer">
                        <span class="status-pill status-info">
                            Kelola kelas
                        </span>

                        <span style="font-weight: 900; color: var(--primary);">
                            →
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</section>
@endsection