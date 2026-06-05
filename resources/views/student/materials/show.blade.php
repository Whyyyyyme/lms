@extends('layouts.app')

@section('title', $material->title)

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $timezone = config('app.timezone', 'Asia/Jakarta');

    $course = $material->kelas?->course;
    $class = $material->kelas;

    $viewer = $viewer ?? [];
    $viewerType = $viewer['type'] ?? null;
    $embedUrl = $viewer['embed_url'] ?? null;
    $downloadUrl = $viewer['download_url'] ?? null;
    $externalUrl = $viewer['url'] ?? null;
    $viewerMessage = $viewer['message'] ?? 'Materi ini belum memiliki file atau link.';

    if ($course && Route::has('student.materials.course')) {
        $backUrl = route('student.materials.course', $course);
        $backLabel = '← Kembali ke Materi '.$course->name;
    } elseif (Route::has('student.materials.index')) {
        $backUrl = route('student.materials.index');
        $backLabel = '← Kembali ke Materi';
    } elseif (Route::has('student.dashboard')) {
        $backUrl = route('student.dashboard');
        $backLabel = '← Dashboard';
    } else {
        $backUrl = '#';
        $backLabel = '← Kembali';
    }

    $publishedAt = $material->published_at
        ? $material->published_at->timezone($timezone)->format('d M Y H:i') . ' WIB'
        : '-';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Mahasiswa</div>

    <h1>{{ $material->title }}</h1>

    <p>
        {{ $course?->name ?? 'Mata kuliah tidak ditemukan' }}

        @if($class?->name)
            · {{ $class->name }}
        @endif

        @if($course?->studySemester)
            · {{ $course->studySemester->name }}
        @endif
    </p>

    <div class="hero-actions">
        <a href="{{ $backUrl }}" class="btn">
            {{ $backLabel }}
        </a>

        @if(!empty($downloadUrl) && $viewerType !== 'youtube')
            <a href="{{ $downloadUrl }}" class="btn btn-primary">
                Download Materi
            </a>
        @endif
    </div>
</section>

<div class="material-view-layout">
    <section class="card">
        @if($viewerType === 'pdf')
            <div class="section-header">
                <div>
                    <h2 class="section-title">PDF Reader</h2>
                    <div class="section-subtitle">
                        Materi PDF dapat dibaca langsung di halaman ini.
                    </div>
                </div>

                @if(!empty($downloadUrl))
                    <a href="{{ $downloadUrl }}" class="btn btn-sm">
                        Download
                    </a>
                @endif
            </div>

            <div
                style="
                    overflow: hidden;
                    border: 1px solid var(--line);
                    border-radius: 20px;
                    background: #f1f5f9;
                "
            >
                <iframe
                    src="{{ $embedUrl }}#toolbar=1&navpanes=0&scrollbar=1"
                    style="width: 100%; height: 75vh; border: 0;"
                    title="PDF Reader {{ $material->title }}"
                ></iframe>
            </div>
        @elseif($viewerType === 'video_iframe')
            <div class="section-header">
                <div>
                    <h2 class="section-title">Video Pembelajaran</h2>
                    <div class="section-subtitle">
                        Video dapat diputar langsung di halaman ini.
                    </div>
                </div>
            </div>

            <div
                style="
                    overflow: hidden;
                    border: 1px solid var(--line);
                    border-radius: 20px;
                    background: #000000;
                    aspect-ratio: 16 / 9;
                "
            >
                <iframe
                    src="{{ $embedUrl }}"
                    style="width: 100%; height: 100%; border: 0;"
                    title="Video {{ $material->title }}"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                    allowfullscreen
                ></iframe>
            </div>
        @elseif($viewerType === 'video_file')
            <div class="section-header">
                <div>
                    <h2 class="section-title">Video Pembelajaran</h2>
                    <div class="section-subtitle">
                        File video dapat diputar langsung di halaman ini.
                    </div>
                </div>
            </div>

            <div
                style="
                    overflow: hidden;
                    border: 1px solid var(--line);
                    border-radius: 20px;
                    background: #000000;
                "
            >
                <video controls style="width: 100%; aspect-ratio: 16 / 9; background: #000000;">
                    <source src="{{ $embedUrl }}">
                    Browser Anda tidak mendukung pemutar video.
                </video>
            </div>
        @elseif($viewerType === 'file')
            <div
                style="
                    padding: 22px;
                    border: 1px solid #fde68a;
                    border-radius: 20px;
                    background: var(--warning-soft);
                    color: #92400e;
                "
            >
                <h2 class="section-title" style="color: #78350f;">
                    Preview tidak tersedia
                </h2>

                <p style="margin-top: 8px; line-height: 1.65;">
                    {{ $viewerMessage }}
                </p>

                @if(!empty($downloadUrl))
                    <div style="margin-top: 16px;">
                        <a href="{{ $downloadUrl }}" class="btn btn-primary">
                            Download File
                        </a>
                    </div>
                @endif
            </div>
        @elseif($viewerType === 'link')
            <div
                style="
                    padding: 22px;
                    border: 1px solid #bfdbfe;
                    border-radius: 20px;
                    background: var(--info-soft);
                    color: #075985;
                "
            >
                <h2 class="section-title" style="color: #0c4a6e;">
                    Link Materi
                </h2>

                <p style="margin-top: 8px; line-height: 1.65;">
                    Materi ini berupa link eksternal. Jika link tersebut bukan YouTube, Google Drive,
                    Vimeo, Loom, PDF, atau file video langsung, sistem tidak bisa menampilkannya langsung.
                </p>

                @if(!empty($externalUrl))
                    <div style="margin-top: 16px;">
                        <a
                            href="{{ $externalUrl }}"
                            target="_blank"
                            rel="noopener"
                            class="btn btn-primary"
                        >
                            Buka Link
                        </a>
                    </div>
                @endif
            </div>
        @else
            <div class="empty-state">
                <div style="font-size: 34px; margin-bottom: 8px;">📘</div>

                <h3 class="empty-state-title">
                    Materi belum tersedia
                </h3>

                <p class="empty-state-text">
                    {{ $viewerMessage }}
                </p>
            </div>
        @endif
    </section>

    <aside style="display: grid; gap: 18px; align-content: start;">
        <section class="card">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Informasi Materi</h2>
                    <div class="section-subtitle">
                        Detail mata kuliah, kelas, tipe materi, dan waktu publikasi.
                    </div>
                </div>

                <span class="status-pill status-info">
                    {{ strtoupper($material->type ?? $viewerType ?? 'Materi') }}
                </span>
            </div>

            <div class="list-stack">
                <div class="list-item">
                    <div>
                        <h3 class="item-title">Mata Kuliah</h3>
                        <div class="item-meta">
                            {{ $course?->name ?? '-' }}

                            @if($course?->code)
                                <br>
                                Kode: {{ $course->code }}
                            @endif
                        </div>
                    </div>
                </div>

                <div class="list-item">
                    <div>
                        <h3 class="item-title">Kelas</h3>
                        <div class="item-meta">
                            {{ $class?->name ?? '-' }}

                            @if($course?->studySemester)
                                <br>
                                Semester {{ $course->studySemester->name }}
                            @endif
                        </div>
                    </div>
                </div>

                <div class="list-item">
                    <div>
                        <h3 class="item-title">Dipublikasikan</h3>
                        <div class="item-meta">
                            {{ $publishedAt }}
                        </div>
                    </div>
                </div>

                <div class="list-item">
                    <div>
                        <h3 class="item-title">Dibuat oleh</h3>
                        <div class="item-meta">
                            {{ $material->creator?->name ?? '-' }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="card">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Deskripsi</h2>
                    <div class="section-subtitle">
                        Ringkasan atau catatan dari asisten.
                    </div>
                </div>
            </div>

            <div
                style="
                    padding: 16px;
                    border: 1px solid var(--line);
                    border-radius: 18px;
                    background: #f8fafc;
                    color: #334155;
                    line-height: 1.75;
                    white-space: pre-line;
                "
            >{{ $material->description ?: 'Tidak ada deskripsi.' }}</div>
        </section>
    </aside>
</div>

<style>
    .material-view-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 420px;
        gap: 22px;
        align-items: start;
    }

    @media (max-width: 1100px) {
        .material-view-layout {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection