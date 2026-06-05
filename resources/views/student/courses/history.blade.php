@extends('layouts.app')

@section('title', 'Riwayat Mata Kuliah')
@section('page_title', 'Riwayat Mata Kuliah')

@section('content')
@php
    use Carbon\Carbon;
    use Illuminate\Support\Facades\Route;

    $statistics = $statistics ?? [];
    $classes = $classes ?? collect();
    $timezone = config('app.timezone', 'Asia/Jakarta');
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Mahasiswa</div>

    <h1>Riwayat Mata Kuliah</h1>

    <p>
        Halaman ini berisi mata kuliah atau kelas praktikum yang pernah kamu ikuti,
        tetapi tahun akademik/semesternya sudah dinonaktifkan. Data tetap bisa dibaca sebagai arsip pembelajaran.
    </p>

    <div class="hero-actions">
        <a href="{{ route('student.courses.index') }}" class="btn">
            ← Mata Kuliah Saya
        </a>

        @if(Route::has('student.materials.history'))
            <a href="{{ route('student.materials.history') }}" class="btn btn-primary">
                📘 Riwayat Materi
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
    Riwayat hanya untuk membaca data lama. Kamu masih bisa membuka materi, tugas, nilai, feedback, dan absensi lama,
    tetapi tidak bisa melakukan submit tugas atau check-in absensi baru dari kelas yang sudah selesai.
</div>

<div class="grid grid-4" style="margin-bottom: 18px;">
    <div class="stat-card">
        <div class="stat-label">Kelas Riwayat</div>
        <div class="stat-value">{{ $statistics['total_classes'] ?? 0 }}</div>
        <div class="stat-note">Kelas dari tahun akademik nonaktif.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Materi Lama</div>
        <div class="stat-value">{{ $statistics['total_materials'] ?? 0 }}</div>
        <div class="stat-note">Materi yang pernah dipublikasikan.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Tugas Lama</div>
        <div class="stat-value">{{ $statistics['total_assignments'] ?? 0 }}</div>
        <div class="stat-note">Tugas dari kelas yang sudah selesai.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Rata-rata Nilai</div>
        <div class="stat-value">
            {{ ($statistics['average_score'] ?? null) !== null ? number_format((float) $statistics['average_score'], 1) : '-' }}
        </div>
        <div class="stat-note">Nilai dari submission yang sudah dinilai.</div>
    </div>
</div>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Riwayat Mata Kuliah / Kelas</h2>
            <div class="section-subtitle">
                Tampilan dibuat mengikuti kartu mata kuliah aktif agar tetap konsisten dengan tema LMS.
            </div>
        </div>
    </div>

    @if($classes->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">🕘</div>

            <h3 class="empty-state-title">Belum ada riwayat mata kuliah</h3>

            <p class="empty-state-text">
                Riwayat akan muncul setelah kamu pernah mengikuti kelas dan tahun akademiknya dinonaktifkan oleh admin.
            </p>
        </div>
    @else
        <div class="course-grid">
            @foreach($classes as $class)
                @php
                    $course = $class->course;
                    $averageScore = $class->average_score;
                    $latestMaterialAt = $class->latest_material_at ?? null;
                @endphp

                <a href="{{ route('student.courses.show', $class) }}" class="course-card">
                    <div>
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                            <span class="course-code">
                                {{ $course?->code ?? 'Mata Kuliah' }}
                            </span>

                            <span class="status-pill status-muted">
                                Riwayat
                            </span>
                        </div>

                        <h3 class="course-title">
                            {{ $course?->name ?? 'Mata kuliah tidak ditemukan' }}
                        </h3>

                        <div class="course-meta">
                            {{ $class->name }}

                            @if($class->room)
                                · Ruang {{ $class->room }}
                            @endif

                            @if($course?->studySemester)
                                <br>
                                Semester {{ $course->studySemester->name }}
                            @endif

                            @if($course?->academicYear)
                                <br>
                                Tahun Akademik {{ $course->academicYear->name }}
                            @endif

                            @if($class->assistant)
                                <br>
                                Asisten {{ $class->assistant->name }}
                            @endif
                        </div>

                        <div class="metric-row">
                            <span class="metric-pill">
                                📘 {{ $class->published_materials_count ?? 0 }} Materi
                            </span>

                            <span class="metric-pill">
                                📝 {{ $class->published_assignments_count ?? 0 }} Tugas
                            </span>

                            <span class="metric-pill">
                                ⭐ {{ $averageScore !== null ? number_format((float) $averageScore, 1) : '-' }} Nilai
                            </span>
                        </div>

                        @if($latestMaterialAt)
                            <div class="item-meta" style="margin-top: 14px;">
                                Materi terakhir:
                                {{ Carbon::parse($latestMaterialAt)->timezone($timezone)->format('d M Y H:i') }} WIB
                            </div>
                        @endif
                    </div>

                    <div class="course-footer">
                        <span class="status-pill status-muted">
                            Buka riwayat
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
