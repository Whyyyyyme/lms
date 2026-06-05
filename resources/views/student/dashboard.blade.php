@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa')
@section('page_title', 'Dashboard Mahasiswa')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $safeRoute = fn (string $name, array $params = [], string $fallback = '#') => Route::has($name)
        ? route($name, $params)
        : $fallback;

    $statistics = $statistics ?? [];
    $classes = collect($classes ?? []);
    $latestMaterials = collect($latestMaterials ?? []);
    $upcomingAssignments = collect($upcomingAssignments ?? []);
    $announcements = collect($announcements ?? []);

    $user = auth()->user();
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Mahasiswa</div>

    <h1>
        Selamat datang, {{ $user?->name ?? 'Mahasiswa' }}
    </h1>

    <p>
        Mulai pembelajaran dari mata kuliah atau kelas praktikum yang kamu ikuti.
        Dari sana kamu bisa mengakses materi, tugas, absensi, pengumuman, nilai, jadwal, dan AI chatbot.
    </p>

    <div class="hero-actions">
        <a href="{{ $safeRoute('student.courses.index') }}" class="btn btn-primary">
            📚 Mata Kuliah Saya
        </a>

        <a href="{{ $safeRoute('student.schedule.index') }}" class="btn">
            🗓️ Jadwal Praktikum
        </a>

        <a href="{{ $safeRoute('student.grades.index') }}" class="btn">
            ⭐ Nilai Saya
        </a>
    </div>
</section>

<div class="grid grid-5" style="margin-bottom: 18px;">
    <div class="stat-card">
        <div class="stat-label">Kelas Aktif</div>
        <div class="stat-value">{{ $statistics['total_kelas'] ?? 0 }}</div>
        <div class="stat-note">Kelas praktikum yang sedang kamu ikuti.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Materi</div>
        <div class="stat-value">{{ $statistics['total_materi'] ?? 0 }}</div>
        <div class="stat-note">Materi yang sudah dipublikasikan.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Tugas</div>
        <div class="stat-value">{{ $statistics['total_tugas'] ?? 0 }}</div>
        <div class="stat-note">Total tugas aktif pada kelas kamu.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Belum Submit</div>
        <div class="stat-value">{{ $statistics['tugas_belum_dikumpulkan'] ?? 0 }}</div>
        <div class="stat-note">Tugas yang belum kamu kumpulkan.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Absensi Buka</div>
        <div class="stat-value">{{ $statistics['absensi_terbuka'] ?? 0 }}</div>
        <div class="stat-note">Sesi absensi yang masih bisa diisi.</div>
    </div>
</div>

<div class="grid grid-4" style="margin-bottom: 22px;">
    <a href="{{ $safeRoute('student.courses.index') }}" class="action-card card">
        <div class="metric-pill">📚 Mata Kuliah</div>
        <h3 class="course-title">Mata Kuliah Saya</h3>
        <p class="course-meta">
            Masuk ke workspace kelas untuk melihat materi, tugas, absensi, dan pengumuman.
        </p>
    </a>

    <a href="{{ $safeRoute('student.schedule.index') }}" class="action-card card">
        <div class="metric-pill">🗓️ Kalender</div>
        <h3 class="course-title">Jadwal Praktikum</h3>
        <p class="course-meta">
            Lihat jadwal kelas, ruangan, deadline tugas, dan waktu absensi.
        </p>
    </a>

    <a href="{{ $safeRoute('student.grades.index') }}" class="action-card card">
        <div class="metric-pill">🏆 Nilai</div>
        <h3 class="course-title">Nilai Saya</h3>
        <p class="course-meta">
            Pantau skor, penilaian, dan feedback dari asisten praktikum.
        </p>
    </a>

    <a href="{{ $safeRoute('student.chatbot.index') }}" class="action-card card">
        <div class="metric-pill">🤖 AI</div>
        <h3 class="course-title">AI Chatbot</h3>
        <p class="course-meta">
            Gunakan chatbot untuk membantu memahami materi dan mengecek aktivitas belajar.
        </p>
    </a>
</div>

<section class="card" style="margin-bottom: 22px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Mata Kuliah / Kelas Saya</h2>
            <div class="section-subtitle">
                Ini menjadi pintu utama untuk masuk ke materi, tugas, absensi, dan pengumuman per kelas.
            </div>
        </div>

        <a href="{{ $safeRoute('student.courses.index') }}" class="btn btn-sm">
            Lihat Semua
        </a>
    </div>

    @if($classes->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">📚</div>
            <h3 class="empty-state-title">Belum ada kelas aktif</h3>
            <p class="empty-state-text">
                Kelas akan muncul sesuai semester dan rombel mahasiswa.
            </p>
        </div>
    @else
        <div class="course-grid">
            @foreach($classes->take(6) as $class)
                <a
                    href="{{ $safeRoute('student.courses.show', ['praktikumClass' => $class->id]) }}"
                    class="course-card"
                >
                    <div>
                        <span class="course-code">
                            {{ $class->course?->code ?? 'Mata Kuliah' }}
                        </span>

                        <h3 class="course-title">
                            {{ $class->course?->name ?? 'Mata kuliah tidak ditemukan' }}
                        </h3>

                        <div class="course-meta">
                            {{ $class->name }}

                            @if($class->room)
                                · Ruang {{ $class->room }}
                            @endif

                            @if($class->course?->studySemester?->name)
                                <br>
                                Semester {{ $class->course->studySemester->name }}
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
                                ⏳ {{ $class->pending_assignments_count ?? 0 }} Belum
                            </span>
                        </div>
                    </div>

                    <div class="course-footer">
                        <span class="status-pill status-info">
                            Masuk kelas
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

<div class="grid grid-3">
    <section class="card">
        <div class="section-header">
            <div>
                <h2 class="section-title">Materi Terbaru</h2>
                <div class="section-subtitle">
                    Materi yang baru dipublikasikan oleh asisten.
                </div>
            </div>

            <a href="{{ $safeRoute('student.courses.index') }}" class="btn btn-sm">
                Mata Kuliah
            </a>
        </div>

        @if($latestMaterials->isEmpty())
            <div class="empty-state">
                <div style="font-size: 30px; margin-bottom: 8px;">📘</div>
                <h3 class="empty-state-title">Belum ada materi</h3>
                <p class="empty-state-text">
                    Materi dari asisten akan tampil di sini.
                </p>
            </div>
        @else
            <div class="list-stack">
                @foreach($latestMaterials as $material)
                    <a
                        href="{{ $safeRoute('student.materials.show', ['material' => $material->id]) }}"
                        class="list-item"
                    >
                        <div>
                            <h3 class="item-title">
                                {{ $material->title }}
                            </h3>

                            <div class="item-meta">
                                {{ $material->kelas->course->name ?? 'Mata kuliah' }}
                                <br>
                                {{ optional($material->published_at)->translatedFormat('d M Y') ?? '-' }}
                            </div>
                        </div>

                        <span class="status-pill status-info">
                            Buka
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="card">
        <div class="section-header">
            <div>
                <h2 class="section-title">Deadline Tugas</h2>
                <div class="section-subtitle">
                    Daftar tugas aktif dengan deadline terdekat.
                </div>
            </div>

            <a href="{{ $safeRoute('student.courses.index') }}" class="btn btn-sm">
                Mata Kuliah
            </a>
        </div>

        @if($upcomingAssignments->isEmpty())
            <div class="empty-state">
                <div style="font-size: 30px; margin-bottom: 8px;">📝</div>
                <h3 class="empty-state-title">Tidak ada deadline</h3>
                <p class="empty-state-text">
                    Tugas aktif akan tampil di sini.
                </p>
            </div>
        @else
            <div class="list-stack">
                @foreach($upcomingAssignments as $assignment)
                    @php
                        $submitted = $assignment->relationLoaded('submissions')
                            ? $assignment->submissions->isNotEmpty()
                            : false;
                    @endphp

                    <a
                        href="{{ $safeRoute('student.assignments.show', ['assignment' => $assignment->id]) }}"
                        class="list-item"
                    >
                        <div>
                            <h3 class="item-title">
                                {{ $assignment->title }}
                            </h3>

                            <div class="item-meta">
                                {{ $assignment->kelas->course->name ?? 'Mata kuliah' }}
                                <br>
                                Deadline:
                                {{ optional($assignment->deadline)->translatedFormat('d M Y H:i') ?? '-' }}
                            </div>
                        </div>

                        <span class="status-pill {{ $submitted ? 'status-success' : 'status-danger' }}">
                            {{ $submitted ? 'Sudah' : 'Belum' }}
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="card">
        <div class="section-header">
            <div>
                <h2 class="section-title">Pengumuman</h2>
                <div class="section-subtitle">
                    Informasi terbaru dari asisten praktikum.
                </div>
            </div>
        </div>

        @if($announcements->isEmpty())
            <div class="empty-state">
                <div style="font-size: 30px; margin-bottom: 8px;">📢</div>
                <h3 class="empty-state-title">Belum ada pengumuman</h3>
                <p class="empty-state-text">
                    Pengumuman dari asisten akan tampil di sini.
                </p>
            </div>
        @else
            <div class="list-stack">
                @foreach($announcements as $announcement)
                    <div class="list-item">
                        <div>
                            <h3 class="item-title">
                                {{ $announcement->title }}
                            </h3>

                            <div class="item-meta">
                                {{ \Illuminate\Support\Str::limit($announcement->content, 130) }}
                                <br>
                                {{ optional($announcement->created_at)->translatedFormat('d M Y H:i') ?? '-' }}
                            </div>
                        </div>

                        <span class="status-pill status-muted">
                            Info
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection