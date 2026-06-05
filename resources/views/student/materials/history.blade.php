@extends('layouts.app')

@section('title', 'Riwayat Materi')

@section('content')
@php
    use Carbon\Carbon;
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Mahasiswa</div>

    <h1>Riwayat Materi</h1>

    <p>
        Pilih mata kuliah riwayat untuk membaca kembali materi dari tahun akademik atau semester yang sudah selesai.
    </p>

    <div class="hero-actions">
        <a href="{{ route('student.materials.index') }}" class="btn">
            ← Materi Aktif
        </a>

        @if(\Illuminate\Support\Facades\Route::has('student.courses.history'))
            <a href="{{ route('student.courses.history') }}" class="btn btn-primary">
                🕘 Riwayat Mata Kuliah
            </a>
        @endif

        @if(\Illuminate\Support\Facades\Route::has('student.assignments.history'))
            <a href="{{ route('student.assignments.history') }}" class="btn">
                📝 Riwayat Tugas
            </a>
        @endif
    </div>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Mata Kuliah Riwayat</h2>
            <div class="section-subtitle">
                Materi lama dikelompokkan per mata kuliah agar alurnya tetap sama seperti halaman materi aktif.
            </div>
        </div>
    </div>

    @if($courses->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">📘</div>

            <h3 class="empty-state-title">Belum ada riwayat materi</h3>

            <p class="empty-state-text">
                Riwayat materi akan muncul setelah ada kelas yang kamu ikuti dan tahun akademiknya dinonaktifkan oleh admin.
            </p>
        </div>
    @else
        <div class="course-grid">
            @foreach($courses as $course)
                <a href="{{ route('student.materials.history-course', $course) }}" class="course-card">
                    <div>
                        <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                            <span class="course-code">
                                {{ $course->code ?? 'Mata Kuliah' }}
                            </span>

                            <span class="status-pill status-muted">
                                Riwayat
                            </span>
                        </div>

                        <h3 class="course-title">
                            {{ $course->name }}
                        </h3>

                        <div class="course-meta">
                            @if($course->studySemester)
                                Semester {{ $course->studySemester->name }}
                            @else
                                Semester belum diatur
                            @endif

                            @if($course->academicYear)
                                <br>
                                Tahun Akademik {{ $course->academicYear->name }}
                            @endif
                        </div>

                        <div class="metric-row">
                            <span class="metric-pill">
                                📘 {{ $course->materials_count ?? 0 }} Materi
                            </span>

                            @if($course->latest_material_at)
                                <span class="metric-pill">
                                    🕒 Update {{ Carbon::parse($course->latest_material_at)->format('d M Y') }}
                                </span>
                            @else
                                <span class="metric-pill">
                                    Belum ada update
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="course-footer">
                        <span class="status-pill status-muted">
                            Lihat riwayat materi
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
