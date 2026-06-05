@extends('layouts.app')

@section('title', 'Mata Kuliah Saya')
@section('page_title', 'Mata Kuliah Saya')

@section('content')
@php
    use Illuminate\Support\Facades\Route;
    use Carbon\Carbon;

    $statistics = $statistics ?? [];
    $classes = $classes ?? collect();
    $upcomingAssignments = $upcomingAssignments ?? collect();
    $openAttendances = $openAttendances ?? collect();

    $timezone = config('app.timezone', 'Asia/Jakarta');

    $dashboardUrl = Route::has('student.dashboard')
        ? route('student.dashboard')
        : '#';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Mahasiswa</div>

    <h1>Mata Kuliah Saya</h1>

    <p>
        Pilih mata kuliah atau kelas praktikum terlebih dahulu.
        Setelah masuk ke kelas, kamu bisa mengakses materi, tugas, absensi, pengumuman, dan nilai kelas tersebut.
    </p>

    <div class="hero-actions">
        <a href="{{ $dashboardUrl }}" class="btn">
            ← Dashboard
        </a>

        @if(Route::has('student.schedule.index'))
            <a href="{{ route('student.schedule.index') }}" class="btn btn-primary">
                🗓️ Jadwal Praktikum
            </a>
        @endif

        @if(Route::has('student.courses.history'))
            <a href="{{ route('student.courses.history') }}" class="btn">
                🕘 Riwayat{{ isset($archivedClassesCount) ? ' ('.$archivedClassesCount.')' : '' }}
            </a>
        @endif
    </div>
</section>

<div class="grid grid-5" style="margin-bottom: 18px;">
    <div class="stat-card">
        <div class="stat-label">Kelas Aktif</div>
        <div class="stat-value">{{ $statistics['total_classes'] ?? 0 }}</div>
        <div class="stat-note">Kelas praktikum yang sedang kamu ikuti.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Materi</div>
        <div class="stat-value">{{ $statistics['total_materials'] ?? 0 }}</div>
        <div class="stat-note">Materi yang sudah dipublikasikan.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Tugas</div>
        <div class="stat-value">{{ $statistics['total_assignments'] ?? 0 }}</div>
        <div class="stat-note">Tugas praktikum dari kelas kamu.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Belum Submit</div>
        <div class="stat-value">{{ $statistics['pending_assignments'] ?? 0 }}</div>
        <div class="stat-note">Tugas yang belum kamu kumpulkan.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Absensi Buka</div>
        <div class="stat-value">{{ $statistics['open_attendances'] ?? 0 }}</div>
        <div class="stat-note">Sesi absensi yang masih tersedia.</div>
    </div>
</div>

<div class="grid grid-3">
    <section class="card" style="grid-column: span 2;">
        <div class="section-header">
            <div>
                <h2 class="section-title">Daftar Mata Kuliah / Kelas</h2>
                <div class="section-subtitle">
                    Data diambil dari semester dan rombel mahasiswa yang sedang login.
                </div>
            </div>
        </div>

        @if($classes->isEmpty())
            <div class="empty-state">
                <div style="font-size: 34px; margin-bottom: 8px;">📚</div>

                <h3 class="empty-state-title">
                    Belum ada mata kuliah
                </h3>

                <p class="empty-state-text">
                    Mata kuliah akan muncul jika akun mahasiswa sudah memiliki semester dan rombel yang sesuai dengan kelas praktikum aktif.
                </p>

                @if(($archivedClassesCount ?? 0) > 0 && Route::has('student.courses.history'))
                    <div style="margin-top: 14px;">
                        <a href="{{ route('student.courses.history') }}" class="btn btn-primary">
                            Lihat Riwayat Mata Kuliah
                        </a>
                    </div>
                @endif
            </div>
        @else
            <div class="course-grid" style="grid-template-columns: repeat(2, minmax(0, 1fr));">
                @foreach($classes as $class)
                    @php
                        $course = $class->course;
                        $nextAssignment = $class->next_assignment ?? null;
                        $averageScore = $class->average_score;
                        $latestMaterialAt = $class->latest_material_at ?? null;
                        $openAttendanceCount = $class->open_attendances_count ?? 0;
                    @endphp

                    <a href="{{ route('student.courses.show', $class) }}" class="course-card">
                        <div>
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px;">
                                <span class="course-code">
                                    {{ $course?->code ?? 'Mata Kuliah' }}
                                </span>

                                @if($openAttendanceCount > 0)
                                    <span class="status-pill status-success">
                                        Absensi Buka
                                    </span>
                                @endif
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

                                @if($class->schedule)
                                    <br>
                                    Jadwal {{ $class->schedule }}
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

                            @if($nextAssignment)
                                <div
                                    style="
                                        margin-top: 14px;
                                        padding: 13px;
                                        border-radius: 16px;
                                        background: var(--warning-soft);
                                        border: 1px solid #fde68a;
                                    "
                                >
                                    <div style="font-size: 12px; font-weight: 900; color: #92400e; text-transform: uppercase; letter-spacing: 0.06em;">
                                        Tugas berikutnya
                                    </div>

                                    <div style="margin-top: 5px; font-weight: 900; color: #0f172a;">
                                        {{ $nextAssignment->title }}
                                    </div>

                                    <div class="item-meta">
                                        Deadline:
                                        {{ $nextAssignment->deadline ? $nextAssignment->deadline->timezone($timezone)->format('d M Y H:i').' WIB' : '-' }}
                                    </div>
                                </div>
                            @elseif($latestMaterialAt)
                                <div class="item-meta" style="margin-top: 14px;">
                                    Materi terakhir:
                                    {{ Carbon::parse($latestMaterialAt)->timezone($timezone)->format('d M Y H:i') }} WIB
                                </div>
                            @endif
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

    <aside style="display: grid; gap: 18px; align-content: start;">
        <section class="card">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Deadline Terdekat</h2>
                    <div class="section-subtitle">
                        Tugas yang perlu segera diperhatikan.
                    </div>
                </div>
            </div>

            @if($upcomingAssignments->isEmpty())
                <div class="empty-state">
                    <div style="font-size: 30px; margin-bottom: 8px;">📝</div>

                    <h3 class="empty-state-title">
                        Belum ada deadline
                    </h3>

                    <p class="empty-state-text">
                        Belum ada deadline tugas terdekat.
                    </p>
                </div>
            @else
                <div class="list-stack">
                    @foreach($upcomingAssignments as $assignment)
                        @php
                            $submitted = $assignment->relationLoaded('submissions')
                                && $assignment->submissions->isNotEmpty();
                        @endphp

                        <a href="{{ route('student.assignments.show', $assignment) }}" class="list-item">
                            <div>
                                <h3 class="item-title">
                                    {{ $assignment->title }}
                                </h3>

                                <div class="item-meta">
                                    {{ $assignment->kelas?->course?->name ?? 'Mata Kuliah' }}
                                    <br>
                                    {{ $assignment->deadline ? $assignment->deadline->timezone($timezone)->format('d M Y H:i').' WIB' : '-' }}
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
                    <h2 class="section-title">Absensi Sedang Dibuka</h2>
                    <div class="section-subtitle">
                        Check-in absensi yang masih tersedia.
                    </div>
                </div>
            </div>

            @if($openAttendances->isEmpty())
                <div class="empty-state">
                    <div style="font-size: 30px; margin-bottom: 8px;">✅</div>

                    <h3 class="empty-state-title">
                        Tidak ada absensi
                    </h3>

                    <p class="empty-state-text">
                        Tidak ada absensi yang sedang dibuka.
                    </p>
                </div>
            @else
                <div class="list-stack">
                    @foreach($openAttendances as $attendance)
                        @php
                            $record = $attendance->records->first();
                            $alreadyPresent = $record?->status === 'hadir';
                        @endphp

                        <div class="list-item">
                            <div>
                                <h3 class="item-title">
                                    {{ $attendance->kelas?->course?->name ?? 'Mata Kuliah' }}
                                </h3>

                                <div class="item-meta">
                                    Ditutup:
                                    {{ $attendance->closed_at ? $attendance->closed_at->timezone($timezone)->format('d M Y H:i').' WIB' : '-' }}
                                </div>

                                <div style="margin-top: 10px;">
                                    @if($alreadyPresent)
                                        <span class="status-pill status-success">
                                            Sudah check-in
                                        </span>
                                    @else
                                        <form action="{{ route('student.attendances.check-in', $attendance) }}" method="POST">
                                            @csrf

                                            <button type="submit" class="btn btn-primary btn-sm">
                                                Check-in
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>
    </aside>
</div>

<style>
    @media (max-width: 1100px) {
        .grid.grid-3 > section[style*="grid-column"] {
            grid-column: span 1 !important;
        }

        .course-grid[style*="repeat(2"] {
            grid-template-columns: 1fr !important;
        }
    }
</style>
@endsection