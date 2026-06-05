@extends('layouts.app')

@section('title', 'Detail Tugas')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $timezone = config('app.timezone', 'Asia/Jakarta');

    $class = $assignment->kelas ?? null;
    $course = $class?->course;

    $deadline = $assignment->deadline
        ? $assignment->deadline->timezone($timezone)->format('d M Y H:i') . ' WIB'
        : '-';

    $isExpired = $assignment->deadline && $assignment->deadline->lessThan(now());
    $isSubmitted = (bool) $submission;
    $isGraded = $submission && $submission->score !== null;
    $isArchivedAssignment = $isArchivedAssignment ?? false;
    $canSubmit = ! $isArchivedAssignment && ! $isExpired;

    if ($class && Route::has('student.courses.show')) {
        $backUrl = route('student.courses.show', $class);
    } elseif (Route::has('student.assignments.index')) {
        $backUrl = route('student.assignments.index');
    } elseif (Route::has('student.dashboard')) {
        $backUrl = route('student.dashboard');
    } else {
        $backUrl = '#';
    }
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Mahasiswa</div>

    <h1>{{ $assignment->title }}</h1>

    <p>
        {{ $course?->name ?? 'Mata kuliah' }}

        @if($class?->name)
            · {{ $class->name }}
        @endif

        · Deadline {{ $deadline }}
    </p>

    <div class="hero-actions">
        <a href="{{ $backUrl }}" class="btn">
            ← Kembali
        </a>

        @if($assignment->file_path)
            <a
                href="{{ asset('storage/'.$assignment->file_path) }}"
                target="_blank"
                class="btn btn-primary"
            >
                Buka File Tugas
            </a>
        @endif
    </div>
</section>

<div class="grid grid-3">
    <section class="card" style="grid-column: span 2;">
        <div class="section-header">
            <div>
                <h2 class="section-title">Instruksi Tugas</h2>
                <div class="section-subtitle">
                    Baca instruksi tugas sebelum mengumpulkan submission.
                </div>
            </div>

            @if($isArchivedAssignment)
                <span class="status-pill status-muted">
                    Riwayat
                </span>
            @elseif($isExpired)
                <span class="status-pill status-danger">
                    Deadline lewat
                </span>
            @else
                <span class="status-pill status-warning">
                    Deadline aktif
                </span>
            @endif
        </div>

        <div class="grid grid-3" style="margin-bottom: 18px;">
            <div class="stat-card">
                <div class="stat-label">Mata Kuliah</div>
                <div class="stat-value" style="font-size: 20px;">
                    {{ $course?->name ?? '-' }}
                </div>
                <div class="stat-note">
                    {{ $course?->code ? 'Kode: '.$course->code : 'Kode mata kuliah belum tersedia.' }}
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Deadline</div>
                <div class="stat-value" style="font-size: 20px;">
                    {{ $deadline }}
                </div>
                <div class="stat-note">
                    Batas akhir pengumpulan tugas.
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Status Submission</div>
                <div class="stat-value" style="font-size: 20px;">
                    {{ $isSubmitted ? 'Sudah' : 'Belum' }}
                </div>
                <div class="stat-note">
                    {{ $isSubmitted ? 'Kamu sudah mengumpulkan tugas.' : 'Kamu belum mengumpulkan tugas.' }}
                </div>
            </div>
        </div>

        <div
            style="
                padding: 18px;
                border: 1px solid var(--line);
                border-radius: 18px;
                background: #f8fafc;
                color: #334155;
                line-height: 1.75;
                white-space: pre-line;
            "
        >{{ $assignment->description ?: 'Tugas ini belum memiliki deskripsi.' }}</div>

        @if($assignment->file_path)
            <div style="margin-top: 18px;">
                <a
                    href="{{ asset('storage/'.$assignment->file_path) }}"
                    target="_blank"
                    class="btn btn-primary"
                >
                    Buka File Tugas
                </a>
            </div>
        @endif
    </section>

    <aside class="card">
        <div class="section-header">
            <div>
                <h2 class="section-title">Submission Saya</h2>
                <div class="section-subtitle">
                    Upload atau perbarui file submission tugas.
                </div>
            </div>
        </div>

        @if($submission)
            <div class="list-stack" style="margin-bottom: 18px;">
                <div class="list-item">
                    <div>
                        <h3 class="item-title">Status</h3>
                        <div class="item-meta">
                            Dikumpulkan:
                            {{ $submission->submitted_at ? $submission->submitted_at->timezone($timezone)->format('d M Y H:i').' WIB' : '-' }}
                        </div>
                    </div>

                    <span class="status-pill status-success">
                        Sudah submit
                    </span>
                </div>

                <div class="list-item">
                    <div>
                        <h3 class="item-title">Nilai</h3>
                        <div class="item-meta">
                            Hasil penilaian dari asisten.
                        </div>
                    </div>

                    @if($isGraded)
                        <span class="status-pill status-success">
                            {{ $submission->score }}
                        </span>
                    @else
                        <span class="status-pill status-warning">
                            Belum dinilai
                        </span>
                    @endif
                </div>
            </div>

            @if($submission->feedback)
                <div class="alert" style="margin-bottom: 18px;">
                    <strong>Feedback:</strong>
                    <br>
                    {{ $submission->feedback }}
                </div>
            @endif
        @else
            <div class="empty-state" style="margin-bottom: 18px;">
                <div style="font-size: 30px; margin-bottom: 8px;">📝</div>

                <h3 class="empty-state-title">
                    Belum submit
                </h3>

                <p class="empty-state-text">
                    Upload file submission untuk mengumpulkan tugas ini.
                </p>
            </div>
        @endif

        @if($canSubmit)
            <form
                action="{{ $submission ? route('student.submissions.update', $submission) : route('student.assignments.submit', $assignment) }}"
                method="POST"
                enctype="multipart/form-data"
                class="form-card"
                style="padding: 0; border: 0; box-shadow: none;"
            >
                @csrf

                @if($submission)
                    @method('PUT')
                @endif

                <div class="form-group">
                    <label for="file" class="form-label">
                        {{ $submission ? 'Ganti File Submission' : 'Upload File Submission' }}
                        <span class="required">*</span>
                    </label>

                    <input
                        id="file"
                        type="file"
                        name="file"
                        class="form-control"
                        required
                    >

                    <div class="form-help">
                        Format: PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, ZIP, RAR, TXT, CSV, JPG, JPEG, atau PNG.
                        Maksimal 100 MB.
                    </div>

                    @error('file')
                        <div class="form-help" style="color: var(--danger);">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    {{ $submission ? 'Update Submission' : 'Kumpulkan Tugas' }}
                </button>
            </form>
        @else
            <div class="alert">
                @if($isArchivedAssignment)
                    Tugas ini berasal dari tahun akademik yang sudah selesai. Kamu masih bisa melihat instruksi, file, nilai, dan feedback, tetapi tidak bisa mengumpulkan atau memperbarui submission.
                @else
                    Deadline tugas sudah berakhir. Kamu masih bisa melihat detail tugas dan hasil penilaian.
                @endif
            </div>
        @endif
    </aside>
</div>

<style>
    @media (max-width: 1100px) {
        .grid.grid-3 > section[style*="grid-column"] {
            grid-column: span 1 !important;
        }
    }
</style>
@endsection