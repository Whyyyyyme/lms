@extends('layouts.app')

@section('title', 'Materi ' . $course->name)

@section('content')
@php
    $totalMaterials = method_exists($materials, 'total')
        ? $materials->total()
        : $materials->count();

    $isHistoryTab = $isHistoryTab ?? false;
    $backUrl = $isHistoryTab && \Illuminate\Support\Facades\Route::has('student.materials.history')
        ? route('student.materials.history')
        : route('student.materials.index');
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Mahasiswa</div>

    <h1>{{ $isHistoryTab ? 'Riwayat Materi' : 'Materi' }} {{ $course->name }}</h1>

    <p>
        @if($isHistoryTab)
            Materi ini berasal dari mata kuliah pada tahun akademik/semester yang sudah selesai.
            Kamu tetap bisa membaca file, link, atau penjelasan lama sebagai arsip pembelajaran.
        @else
            Daftar materi praktikum berdasarkan mata kuliah yang kamu pilih.
            Buka materi untuk melihat file, link, atau penjelasan yang sudah dipublikasikan oleh asisten.
        @endif
    </p>

    <div class="hero-actions">
        <a href="{{ $backUrl }}" class="btn">
            ← {{ $isHistoryTab ? 'Riwayat Materi' : 'Kembali ke Mata Kuliah' }}
        </a>

        @if(\Illuminate\Support\Facades\Route::has('student.dashboard'))
            <a href="{{ route('student.dashboard') }}" class="btn btn-primary">
                🏠 Dashboard
            </a>
        @endif
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Informasi Mata Kuliah</h2>
            <div class="section-subtitle">
                Ringkasan mata kuliah dan jumlah materi yang tersedia.
            </div>
        </div>
    </div>

    <div class="grid grid-4">
        <div class="stat-card">
            <div class="stat-label">Kode Mata Kuliah</div>
            <div class="stat-value" style="font-size: 22px;">
                {{ $course->code ?? '-' }}
            </div>
            <div class="stat-note">Kode identitas mata kuliah.</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Semester</div>
            <div class="stat-value" style="font-size: 22px;">
                {{ $course->studySemester?->name ?? '-' }}
            </div>
            <div class="stat-note">Semester mahasiswa terkait.</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Tahun Akademik</div>
            <div class="stat-value" style="font-size: 22px;">
                {{ $course->academicYear?->name ?? '-' }}
            </div>
            <div class="stat-note">Periode akademik mata kuliah.</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Total Materi</div>
            <div class="stat-value">
                {{ $totalMaterials }}
            </div>
            <div class="stat-note">Materi yang sudah tersedia.</div>
        </div>
    </div>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">{{ $isHistoryTab ? 'Daftar Riwayat Materi' : 'Daftar Materi' }}</h2>
            <div class="section-subtitle">
                {{ $isHistoryTab ? 'Materi lama ditampilkan dari yang tersedia pada mata kuliah riwayat ini.' : 'Materi ditampilkan dari yang tersedia untuk mata kuliah ini.' }}
            </div>
        </div>

        <a href="{{ $backUrl }}" class="btn btn-sm">
            {{ $isHistoryTab ? 'Semua Riwayat Materi' : 'Semua Mata Kuliah' }}
        </a>
    </div>

    @if($materials->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">📘</div>

            <h3 class="empty-state-title">
                {{ $isHistoryTab ? 'Belum ada riwayat materi' : 'Belum ada materi' }}
            </h3>

            <p class="empty-state-text">
                {{ $isHistoryTab ? 'Belum ada materi lama yang tersimpan untuk mata kuliah ini.' : 'Belum ada materi yang dipublikasikan untuk mata kuliah ini.' }}
            </p>
        </div>
    @else
        <div class="course-grid">
            @foreach($materials as $material)
                <a
                    href="{{ route('student.materials.show', $material) }}"
                    class="course-card"
                >
                    <div>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap;">
                            <span class="course-code">
                                {{ strtoupper($material->type ?? 'Materi') }}
                            </span>

                            <span class="status-pill status-muted">
                                {{ $material->published_at?->format('d M Y') ?? '-' }}
                            </span>
                        </div>

                        <h3 class="course-title">
                            {{ $material->title }}
                        </h3>

                        <div class="course-meta">
                            {{ $material->kelas?->name ?? 'Kelas praktikum' }}

                            @if($material->kelas?->room)
                                · Ruang {{ $material->kelas->room }}
                            @endif
                        </div>

                        @if($material->description)
                            <p class="course-meta" style="margin-top: 12px;">
                                {{ \Illuminate\Support\Str::limit($material->description, 140) }}
                            </p>
                        @else
                            <p class="course-meta" style="margin-top: 12px;">
                                Materi ini belum memiliki deskripsi.
                            </p>
                        @endif
                    </div>

                    <div class="course-footer">
                        <span class="status-pill {{ $isHistoryTab ? 'status-muted' : 'status-info' }}">
                            {{ $isHistoryTab ? 'Buka riwayat' : 'Buka materi' }}
                        </span>

                        <span style="font-weight: 900; color: var(--primary);">
                            →
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        <div style="margin-top: 18px;">
            {{ $materials->links() }}
        </div>
    @endif
</section>
@endsection