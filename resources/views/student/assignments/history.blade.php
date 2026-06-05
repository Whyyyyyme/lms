@extends('layouts.app')

@section('title', 'Riwayat Tugas')

@section('content')
<section class="dashboard-hero">
    <div class="eyebrow">Mahasiswa</div>

    <h1>Riwayat Tugas</h1>

    <p>
        Halaman ini menampilkan tugas lama dari kelas yang tahun akademik atau semesternya sudah selesai.
        Tugas riwayat hanya bisa dilihat, tidak bisa dikumpulkan ulang.
    </p>

    <div class="hero-actions">
        <a href="{{ route('student.assignments.index') }}" class="btn">
            ← Tugas Aktif
        </a>

        @if(\Illuminate\Support\Facades\Route::has('student.courses.history'))
            <a href="{{ route('student.courses.history') }}" class="btn btn-primary">
                🕘 Riwayat Mata Kuliah
            </a>
        @endif

        @if(\Illuminate\Support\Facades\Route::has('student.materials.history'))
            <a href="{{ route('student.materials.history') }}" class="btn">
                📘 Riwayat Materi
            </a>
        @endif
    </div>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Riwayat Tugas</h2>
            <div class="section-subtitle">
                Kamu tetap bisa membuka detail tugas, melihat submission, nilai, dan feedback lama.
            </div>
        </div>
    </div>

    @if($assignments->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">📝</div>

            <h3 class="empty-state-title">Belum ada riwayat tugas</h3>

            <p class="empty-state-text">
                Riwayat tugas akan muncul setelah ada kelas yang kamu ikuti dan tahun akademiknya dinonaktifkan oleh admin.
            </p>
        </div>
    @else
        <div class="list-stack">
            @foreach($assignments as $assignment)
                @php
                    $isSubmitted = $assignment->submissions->isNotEmpty();
                    $deadline = $assignment->deadline;
                @endphp

                <a href="{{ route('student.assignments.show', $assignment) }}" class="list-item">
                    <div style="min-width: 0;">
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 8px;">
                            <span class="course-code">
                                {{ $assignment->kelas?->course?->code ?? 'Tugas' }}
                            </span>

                            <span class="status-pill status-muted">
                                Riwayat
                            </span>

                            @if($isSubmitted)
                                <span class="status-pill status-success">
                                    Sudah dikumpulkan
                                </span>
                            @endif
                        </div>

                        <h3 class="item-title">
                            {{ $assignment->title }}
                        </h3>

                        <div class="item-meta">
                            {{ $assignment->kelas?->course?->name ?? 'Mata kuliah tidak ditemukan' }}

                            @if($assignment->kelas?->name)
                                · {{ $assignment->kelas->name }}
                            @endif

                            @if($assignment->kelas?->course?->academicYear)
                                <br>
                                Tahun Akademik {{ $assignment->kelas->course->academicYear->name }}
                            @endif

                            <br>
                            Deadline:
                            <strong>
                                {{ $deadline?->format('d M Y H:i') ?? '-' }}
                            </strong>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px; flex-shrink: 0;">
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

        <div style="margin-top: 18px;">
            {{ $assignments->links() }}
        </div>
    @endif
</section>
@endsection
