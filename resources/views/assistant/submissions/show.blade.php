@extends('layouts.app')

@section('title', 'Nilai Submission')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $timezone = config('app.timezone', 'Asia/Jakarta');

    $submittedAt = $submission->submitted_at
        ? $submission->submitted_at->timezone($timezone)->format('d M Y H:i') . ' WIB'
        : '-';

    $backUrl = Route::has('assistant.submissions.index')
        ? route('assistant.submissions.index')
        : route('assistant.dashboard');

    $isGraded = $submission->score !== null;
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Asisten Praktikum</div>

    <h1>Nilai Submission</h1>

    <p>
        Periksa submission mahasiswa, berikan nilai, dan tambahkan feedback agar mahasiswa
        mengetahui hasil penilaian tugasnya.
    </p>

    <div class="hero-actions">
        <a href="{{ $backUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('assistant.submissions.index'))
            <a href="{{ route('assistant.submissions.index') }}" class="btn btn-primary">
                📥 Semua Submission
            </a>
        @endif
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Detail Submission</h2>
            <div class="section-subtitle">
                Informasi mahasiswa, tugas, waktu pengumpulan, dan file submission.
            </div>
        </div>

        <span class="status-pill {{ $isGraded ? 'status-success' : 'status-warning' }}">
            {{ $isGraded ? 'Sudah dinilai' : 'Belum dinilai' }}
        </span>
    </div>

    <div class="grid grid-4">
        <div class="stat-card">
            <div class="stat-label">Mahasiswa</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $submission->student?->name ?? '-' }}
            </div>

            <div class="stat-note">
                {{ $submission->student?->nim_nip ?? 'NIM/NIP tidak tersedia.' }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Tugas</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $submission->assignment?->title ?? '-' }}
            </div>

            <div class="stat-note">
                {{ $submission->assignment?->kelas?->course?->name ?? 'Mata kuliah tidak tersedia.' }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Dikumpulkan</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $submittedAt }}
            </div>

            <div class="stat-note">
                Waktu mahasiswa mengirim submission.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Nilai Saat Ini</div>
            <div class="stat-value">
                {{ $submission->score ?? '-' }}
            </div>

            <div class="stat-note">
                {{ $submission->assignment?->max_score ? 'Nilai maksimal: '.$submission->assignment->max_score : 'Nilai maksimal belum tersedia.' }}
            </div>
        </div>
    </div>

    @if($submission->file_path)
        <div style="margin-top: 18px;">
            <a
                href="{{ asset('storage/'.$submission->file_path) }}"
                target="_blank"
                class="btn btn-primary"
            >
                Download Submission
            </a>
        </div>
    @endif
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Form Penilaian</h2>
            <div class="section-subtitle">
                Isi nilai dan feedback untuk submission mahasiswa.
            </div>
        </div>
    </div>

    <form
        action="{{ route('assistant.submissions.grade', $submission) }}"
        method="POST"
        class="form-card"
    >
        @csrf
        @method('PATCH')

        <div class="form-grid">
            <div class="form-group">
                <label for="score" class="form-label">
                    Nilai <span class="required">*</span>
                </label>

                <input
                    id="score"
                    type="number"
                    name="score"
                    class="form-control"
                    value="{{ old('score', $submission->score) }}"
                    min="0"
                    @if($submission->assignment?->max_score)
                        max="{{ $submission->assignment->max_score }}"
                    @endif
                    required
                >

                <div class="form-help">
                    Masukkan nilai submission mahasiswa.
                    @if($submission->assignment?->max_score)
                        Nilai maksimal: {{ $submission->assignment->max_score }}.
                    @endif
                </div>

                @error('score')
                    <div class="form-help" style="color: var(--danger);">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-label">
                    Status Penilaian
                </label>

                <div style="padding-top: 4px;">
                    <span class="status-pill {{ $isGraded ? 'status-success' : 'status-warning' }}">
                        {{ $isGraded ? 'Sudah dinilai' : 'Belum dinilai' }}
                    </span>
                </div>

                <div class="form-help">
                    Status berubah setelah nilai berhasil disimpan.
                </div>
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="feedback" class="form-label">
                    Feedback
                </label>

                <textarea
                    id="feedback"
                    name="feedback"
                    class="form-control"
                    placeholder="Tuliskan feedback, koreksi, atau catatan untuk mahasiswa."
                >{{ old('feedback', $submission->feedback) }}</textarea>

                <div class="form-help">
                    Feedback bersifat opsional, tetapi disarankan agar mahasiswa memahami hasil penilaiannya.
                </div>

                @error('feedback')
                    <div class="form-help" style="color: var(--danger);">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ $backUrl }}" class="btn">
                Batal
            </a>

            <button type="submit" class="btn btn-primary">
                Simpan Nilai
            </button>
        </div>
    </form>
</section>
@endsection