@extends('layouts.app')

@section('title', $class->course?->name ?? 'Detail Mata Kuliah')
@section('page_title', $class->course?->name ?? 'Detail Mata Kuliah')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $course = $class->course;
    $timezone = config('app.timezone', 'Asia/Jakarta');
    $isArchivedCourse = $isArchivedCourse ?? false;

    $backUrl = $isArchivedCourse && Route::has('student.courses.history')
        ? route('student.courses.history')
        : (Route::has('student.courses.index')
            ? route('student.courses.index')
            : route('student.dashboard'));

    $scheduleUrl = Route::has('student.schedule.index')
        ? route('student.schedule.index', ['mata_kuliah' => $course?->id])
        : '#';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Mahasiswa</div>

    <h1>
        {{ $course?->name ?? 'Mata kuliah tidak ditemukan' }}
    </h1>

    <p>
        {{ $course?->code ? $course->code . ' · ' : '' }}
        {{ $class->name }}

        @if($course?->studySemester)
            · {{ $course->studySemester->name }}
        @endif

        @if($course?->academicYear)
            · {{ trim(($course->academicYear->year ?? '') . ' ' . ($course->academicYear->semester ?? '')) }}
        @endif
    </p>

    <div class="hero-actions">
        <a href="{{ $backUrl }}" class="btn">
            ← {{ $isArchivedCourse ? 'Riwayat Mata Kuliah' : 'Mata Kuliah Saya' }}
        </a>

        <a href="#materi" class="btn btn-primary">
            📘 Lihat Materi
        </a>

        <a href="#tugas" class="btn">
            📝 Tugas
        </a>

        <a href="#absensi" class="btn">
            ✅ Absensi
        </a>
    </div>
</section>

@if($isArchivedCourse)
    <div class="alert" style="margin-bottom: 18px;">
        Mata kuliah ini berasal dari tahun akademik yang sudah selesai. Kamu tetap bisa membaca materi, tugas, absensi, pengumuman, nilai, dan feedback, tetapi aktivitas baru seperti check-in absensi atau submit tugas tidak tersedia.
    </div>
@endif

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Informasi Kelas Praktikum</h2>
            <div class="section-subtitle">
                Detail kelas, asisten, jadwal, ruang, dan progress tugas pada mata kuliah ini.
            </div>
        </div>

        <span class="status-pill {{ $isArchivedCourse ? 'status-muted' : 'status-info' }}">
            {{ $isArchivedCourse ? 'Riwayat' : $class->name }}
        </span>
    </div>

    <div class="grid grid-4">
        <div class="stat-card">
            <div class="stat-label">Kode Mata Kuliah</div>
            <div class="stat-value" style="font-size: 22px;">
                {{ $course?->code ?? '-' }}
            </div>
            <div class="stat-note">Kode identitas mata kuliah.</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Asisten</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $class->assistant?->name ?? '-' }}
            </div>
            <div class="stat-note">Asisten pengampu kelas ini.</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Jadwal</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $class->schedule ?: '-' }}
            </div>
            <div class="stat-note">Jadwal praktikum kelas.</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Ruang</div>
            <div class="stat-value" style="font-size: 22px;">
                {{ $class->room ?: '-' }}
            </div>
            <div class="stat-note">Ruang praktikum.</div>
        </div>
    </div>
</section>

<div class="grid grid-5" style="margin-bottom: 18px;">
    <div class="stat-card">
        <div class="stat-label">Materi</div>
        <div class="stat-value">{{ $summary['total_materials'] ?? 0 }}</div>
        <div class="stat-note">Materi yang tersedia.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Tugas</div>
        <div class="stat-value">{{ $summary['total_assignments'] ?? 0 }}</div>
        <div class="stat-note">Total tugas kelas.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Belum Submit</div>
        <div class="stat-value">{{ $summary['pending_assignments'] ?? 0 }}</div>
        <div class="stat-note">Tugas yang belum dikumpulkan.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Absensi Buka</div>
        <div class="stat-value">{{ $summary['open_attendances'] ?? 0 }}</div>
        <div class="stat-note">Sesi absensi aktif.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Rata-rata Nilai</div>
        <div class="stat-value">
            {{ ($summary['average_score'] ?? null) !== null ? number_format((float) $summary['average_score'], 1) : '-' }}
        </div>
        <div class="stat-note">
            Progress tugas: {{ $summary['progress'] ?? 0 }}%.
        </div>
    </div>
</div>

<div class="grid grid-3">
    <section id="materi" class="card" style="grid-column: span 2;">
        <div class="section-header">
            <div>
                <h2 class="section-title">Materi Pembelajaran</h2>
                <div class="section-subtitle">
                    Materi yang sudah dipublikasikan oleh asisten untuk kelas ini.
                </div>
            </div>
        </div>

        @if($materials->isEmpty())
            <div class="empty-state">
                <div style="font-size: 34px; margin-bottom: 8px;">📘</div>

                <h3 class="empty-state-title">
                    Belum ada materi
                </h3>

                <p class="empty-state-text">
                    Materi untuk kelas ini akan tampil setelah dipublikasikan oleh asisten.
                </p>
            </div>
        @else
            <div class="list-stack">
                @foreach($materials as $material)
                    <a href="{{ route('student.materials.show', $material) }}" class="list-item">
                        <div>
                            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 8px;">
                                <span class="course-code">
                                    {{ strtoupper($material->type ?? 'materi') }}
                                </span>

                                <span class="status-pill status-muted">
                                    {{ $material->published_at?->timezone($timezone)->format('d M Y H:i') ?? '-' }} WIB
                                </span>
                            </div>

                            <h3 class="item-title">
                                {{ $material->title }}
                            </h3>

                            @if($material->description)
                                <div class="item-meta">
                                    {{ \Illuminate\Support\Str::limit($material->description, 130) }}
                                </div>
                            @endif
                        </div>

                        <span class="status-pill status-info">
                            Buka materi
                        </span>
                    </a>
                @endforeach
            </div>

            <div style="margin-top: 18px;">
                {{ $materials->links() }}
            </div>
        @endif
    </section>

    <aside style="display: grid; gap: 18px; align-content: start;">
        <section class="card">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Pengumuman</h2>
                    <div class="section-subtitle">
                        Informasi dari asisten untuk kelas ini.
                    </div>
                </div>
            </div>

            @if($announcements->isEmpty())
                <div class="empty-state">
                    <div style="font-size: 30px; margin-bottom: 8px;">📢</div>

                    <h3 class="empty-state-title">
                        Belum ada pengumuman
                    </h3>

                    <p class="empty-state-text">
                        Belum ada pengumuman untuk kelas ini.
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
                                    {{ $announcement->created_at?->timezone($timezone)->format('d M Y H:i') ?? '-' }} WIB
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </section>

        <section class="card">
            <div class="section-header">
                <div>
                    <h2 class="section-title">Navigasi Kelas</h2>
                    <div class="section-subtitle">
                        Lompat ke bagian halaman.
                    </div>
                </div>
            </div>

            <div class="list-stack">
                <a href="#materi" class="list-item">
                    <div>
                        <h3 class="item-title">Materi</h3>
                        <div class="item-meta">Lihat materi kelas.</div>
                    </div>
                    <span class="status-pill status-info">Buka</span>
                </a>

                <a href="#tugas" class="list-item">
                    <div>
                        <h3 class="item-title">Tugas</h3>
                        <div class="item-meta">Lihat tugas kelas.</div>
                    </div>
                    <span class="status-pill status-info">Buka</span>
                </a>

                <a href="#absensi" class="list-item">
                    <div>
                        <h3 class="item-title">Absensi</h3>
                        <div class="item-meta">Lihat sesi absensi.</div>
                    </div>
                    <span class="status-pill status-info">Buka</span>
                </a>

                @if(Route::has('student.schedule.index'))
                    <a href="{{ $scheduleUrl }}" class="list-item">
                        <div>
                            <h3 class="item-title">Jadwal/Kalender</h3>
                            <div class="item-meta">Lihat jadwal praktikum.</div>
                        </div>
                        <span class="status-pill status-info">Buka</span>
                    </a>
                @endif
            </div>
        </section>
    </aside>
</div>

<section id="tugas" class="card" style="margin-top: 22px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Tugas Kelas</h2>
            <div class="section-subtitle">
                Upload submission dilakukan dari halaman detail tugas.
            </div>
        </div>
    </div>

    @if($assignments->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">📝</div>

            <h3 class="empty-state-title">
                Belum ada tugas
            </h3>

            <p class="empty-state-text">
                Tugas yang dipublikasikan asisten akan tampil di bagian ini.
            </p>
        </div>
    @else
        <div class="list-stack">
            @foreach($assignments as $assignment)
                @php
                    $submission = $assignment->submissions->first();
                    $submitted = $submission !== null;
                    $isExpired = $assignment->deadline && $assignment->deadline->lessThan(now());
                @endphp

                <a href="{{ route('student.assignments.show', $assignment) }}" class="list-item">
                    <div>
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 8px;">
                            <span class="status-pill {{ $submitted ? 'status-success' : 'status-warning' }}">
                                {{ $submitted ? 'Sudah dikumpulkan' : 'Belum dikumpulkan' }}
                            </span>

                            @if($isExpired)
                                <span class="status-pill status-danger">
                                    Deadline lewat
                                </span>
                            @endif
                        </div>

                        <h3 class="item-title">
                            {{ $assignment->title }}
                        </h3>

                        @if($assignment->description)
                            <div class="item-meta">
                                {{ \Illuminate\Support\Str::limit($assignment->description, 130) }}
                            </div>
                        @endif

                        <div class="item-meta">
                            Deadline:
                            {{ $assignment->deadline?->timezone($timezone)->format('d M Y H:i') ?? '-' }} WIB
                        </div>
                    </div>

                    <span class="status-pill status-info">
                        Buka tugas
                    </span>
                </a>
            @endforeach
        </div>

        <div style="margin-top: 18px;">
            {{ $assignments->links() }}
        </div>
    @endif
</section>

<section id="absensi" class="card" style="margin-top: 22px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Absensi Kelas</h2>
            <div class="section-subtitle">
                Check-in hanya tersedia saat waktu absensi sedang dibuka.
            </div>
        </div>
    </div>

    @if($attendances->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">✅</div>

            <h3 class="empty-state-title">
                Belum ada sesi absensi
            </h3>

            <p class="empty-state-text">
                Belum ada sesi absensi untuk kelas ini.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu Absensi</th>
                            <th>Status Sesi</th>
                            <th>Status Kamu</th>
                            <th>Check-in</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($attendances as $attendance)
                            @php
                                $record = $attendance->records->first();
                                $studentStatus = $record?->status ?? 'alpha';

                                $studentStatusLabel = match ($studentStatus) {
                                    'hadir' => 'Hadir',
                                    'izin' => 'Izin',
                                    default => 'Alpha',
                                };

                                $studentStatusClass = match ($studentStatus) {
                                    'hadir' => 'status-success',
                                    'izin' => 'status-info',
                                    default => 'status-danger',
                                };

                                $sessionStatus = method_exists($attendance, 'statusLabel')
                                    ? $attendance->statusLabel()
                                    : ($attendance->is_open ? 'Sedang Dibuka' : 'Ditutup');

                                $rawSessionStatusClass = method_exists($attendance, 'statusBadgeClass')
                                    ? $attendance->statusBadgeClass()
                                    : ($attendance->is_open ? 'badge-green' : 'badge-red');

                                $sessionStatusClass = match ($rawSessionStatusClass) {
                                    'badge-green' => 'status-success',
                                    'badge-blue' => 'status-info',
                                    'badge-red' => 'status-danger',
                                    'badge-yellow' => 'status-warning',
                                    default => 'status-muted',
                                };

                                $canCheckIn = ! $isArchivedCourse && (
                                    method_exists($attendance, 'isWithinOpenWindow')
                                        ? $attendance->isWithinOpenWindow() && $studentStatus === 'alpha'
                                        : $attendance->is_open && $studentStatus === 'alpha'
                                );
                            @endphp

                            <tr>
                                <td>
                                    <div class="item-meta" style="margin-top: 0;">
                                        <strong>Dibuka:</strong>
                                        {{ $attendance->opened_at?->timezone($timezone)->format('d M Y H:i') ?? '-' }} WIB
                                        <br>
                                        <strong>Ditutup:</strong>
                                        {{ $attendance->closed_at?->timezone($timezone)->format('d M Y H:i') ?? '-' }} WIB
                                    </div>
                                </td>

                                <td>
                                    <span class="status-pill {{ $sessionStatusClass }}">
                                        {{ $sessionStatus }}
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill {{ $studentStatusClass }}">
                                        {{ $studentStatusLabel }}
                                    </span>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $record?->checked_at?->timezone($timezone)->format('d M Y H:i') ?? '-' }} WIB
                                    </span>
                                </td>

                                <td>
                                    @if($canCheckIn)
                                        <form action="{{ route('student.attendances.check-in', $attendance) }}" method="POST">
                                            @csrf

                                            <button type="submit" class="btn btn-primary btn-sm">
                                                Check-in
                                            </button>
                                        </form>
                                    @elseif($studentStatus === 'hadir')
                                        <span class="status-pill status-success">
                                            Sudah check-in
                                        </span>
                                    @elseif($studentStatus === 'izin')
                                        <span class="status-pill status-info">
                                            Izin
                                        </span>
                                    @else
                                        <span class="status-pill status-muted">
                                            Tidak tersedia
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 18px;">
            {{ $attendances->links() }}
        </div>
    @endif
</section>

<style>
    @media (max-width: 1100px) {
        .grid.grid-3 > section[style*="grid-column"] {
            grid-column: span 1 !important;
        }
    }
</style>
@endsection