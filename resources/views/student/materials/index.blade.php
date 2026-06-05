@extends('layouts.app')

@section('title', 'Materi Praktikum')

@section('content')
@php
    use Carbon\Carbon;
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Mahasiswa</div>

    <h1>Materi Praktikum</h1>

    <p>
        Pilih mata kuliah untuk melihat materi praktikum yang sudah dipublikasikan oleh asisten.
        Materi ditampilkan berdasarkan mata kuliah dan kelas yang bisa kamu akses.
    </p>

    <div class="hero-actions">
        <a href="{{ route('student.dashboard') }}" class="btn">
            ← Dashboard
        </a>

        @if(\Illuminate\Support\Facades\Route::has('student.courses.index'))
            <a href="{{ route('student.courses.index') }}" class="btn btn-primary">
                📚 Mata Kuliah Saya
            </a>
        @endif
    </div>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Mata Kuliah</h2>
            <div class="section-subtitle">
                Masuk ke salah satu mata kuliah untuk melihat daftar materi praktikum.
            </div>
        </div>
    </div>

    @if($courses->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">📚</div>

            <h3 class="empty-state-title">
                Belum ada mata kuliah
            </h3>

            <p class="empty-state-text">
                Mata kuliah akan muncul sesuai semester mahasiswa dan akses kelas praktikum.
            </p>
        </div>
    @else
        <div class="course-grid">
            @foreach($courses as $course)
                <a
                    href="{{ route('student.materials.course', $course) }}"
                    class="course-card"
                >
                    <div>
                        <span class="course-code">
                            {{ $course->code ?? 'Mata Kuliah' }}
                        </span>

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
                        <span class="status-pill status-info">
                            Lihat materi
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