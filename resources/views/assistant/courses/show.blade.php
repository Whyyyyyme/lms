@extends('layouts.app')

@section('title', 'Kelola Mata Kuliah')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $course = $class->course;
    $semester = $course?->studySemester;
    $timezone = config('app.timezone', 'Asia/Jakarta');

    $courseName = $course?->name ?? 'Mata Kuliah';
    $courseCode = $course?->code;
    $semesterName = $semester?->name;

    $description = trim(
        ($courseCode ? $courseCode.' · ' : '') .
        $class->name .
        ($semesterName ? ' · '.$semesterName : '')
    );

    $backUrl = Route::has('assistant.courses.index')
        ? route('assistant.courses.index')
        : route('assistant.dashboard');

    $createMaterialUrl = route('assistant.materi.create', ['class_id' => $class->id]);
    $createAssignmentUrl = route('assistant.tugas.create', ['class_id' => $class->id]);
    $createAttendanceUrl = route('assistant.attendances.create', ['class_id' => $class->id]);
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Asisten Praktikum</div>

    <h1>{{ $courseName }}</h1>

    <p>
        {{ $description ?: 'Ruang kelola kelas praktikum.' }}
        Semua data di halaman ini hanya untuk mata kuliah dan kelas yang sedang dipilih.
    </p>

    <div class="hero-actions">
        <a href="{{ $backUrl }}" class="btn">
            ← Mata Kuliah
        </a>

        <a href="{{ $createMaterialUrl }}" class="btn btn-primary">
            📘 Tambah Materi
        </a>

        <a href="{{ $createAssignmentUrl }}" class="btn">
            📝 Buat Tugas
        </a>

        <a href="{{ $createAttendanceUrl }}" class="btn">
            ✅ Buat Absensi
        </a>
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Ruang Kelola Kelas</h2>
            <div class="section-subtitle">
                Asisten tidak perlu memilih kelas lagi saat membuat materi, tugas, atau absensi dari halaman ini.
            </div>
        </div>

        <span class="status-pill status-info">
            {{ $class->name }}
        </span>
    </div>

    <div class="grid grid-4">
        <div class="stat-card">
            <div class="stat-label">Kode Mata Kuliah</div>
            <div class="stat-value" style="font-size: 22px;">
                {{ $courseCode ?? '-' }}
            </div>
            <div class="stat-note">Kode identitas mata kuliah.</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Semester</div>
            <div class="stat-value" style="font-size: 22px;">
                {{ $semesterName ?? '-' }}
            </div>
            <div class="stat-note">Semester mahasiswa terkait.</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Ruang</div>
            <div class="stat-value" style="font-size: 22px;">
                {{ $class->room ?: '-' }}
            </div>
            <div class="stat-note">Ruang praktikum yang digunakan.</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Jadwal</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $class->schedule ?: '-' }}
            </div>
            <div class="stat-note">Jadwal praktikum kelas ini.</div>
        </div>
    </div>
</section>

<div class="grid grid-4" style="margin-bottom: 18px;">
    <div class="stat-card">
        <div class="stat-label">Mahasiswa</div>
        <div class="stat-value">{{ $statistics['total_mahasiswa'] ?? 0 }}</div>
        <div class="stat-note">Total mahasiswa pada kelas ini.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Materi</div>
        <div class="stat-value">{{ $statistics['total_materi'] ?? 0 }}</div>
        <div class="stat-note">Materi yang dibuat untuk kelas ini.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Tugas</div>
        <div class="stat-value">{{ $statistics['total_tugas'] ?? 0 }}</div>
        <div class="stat-note">Tugas praktikum pada kelas ini.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Absensi</div>
        <div class="stat-value">{{ $statistics['total_absensi'] ?? 0 }}</div>
        <div class="stat-note">Sesi absensi yang sudah dibuat.</div>
    </div>
</div>

<section class="card" style="margin-bottom: 22px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Aksi Cepat Kelas</h2>
            <div class="section-subtitle">
                Buat data baru langsung untuk kelas praktikum ini.
            </div>
        </div>
    </div>

    <div class="grid grid-3">
        <a href="{{ $createMaterialUrl }}" class="action-card card">
            <div class="metric-pill">📘 Materi</div>

            <h3 class="course-title">Tambah Materi</h3>

            <p class="course-meta">
                Upload PDF atau simpan link materi untuk kelas ini.
            </p>

            <div class="course-footer">
                <span class="status-pill status-info">Tambah materi</span>
                <span style="font-weight: 900; color: var(--primary);">→</span>
            </div>
        </a>

        <a href="{{ $createAssignmentUrl }}" class="action-card card">
            <div class="metric-pill">📝 Tugas</div>

            <h3 class="course-title">Buat Tugas</h3>

            <p class="course-meta">
                Buat tugas dan deadline khusus untuk kelas ini.
            </p>

            <div class="course-footer">
                <span class="status-pill status-info">Buat tugas</span>
                <span style="font-weight: 900; color: var(--primary);">→</span>
            </div>
        </a>

        <a href="{{ $createAttendanceUrl }}" class="action-card card">
            <div class="metric-pill">✅ Absensi</div>

            <h3 class="course-title">Buat Absensi</h3>

            <p class="course-meta">
                Jadwalkan waktu buka dan tutup absensi kelas ini.
            </p>

            <div class="course-footer">
                <span class="status-pill status-info">Buat absensi</span>
                <span style="font-weight: 900; color: var(--primary);">→</span>
            </div>
        </a>
    </div>
</section>

<section class="card" style="margin-bottom: 22px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Materi</h2>
            <div class="section-subtitle">
                Daftar materi yang sudah dibuat untuk kelas ini.
            </div>
        </div>

        <a href="{{ $createMaterialUrl }}" class="btn btn-primary btn-sm">
            + Materi
        </a>
    </div>

    @if($materials->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">📘</div>
            <h3 class="empty-state-title">Belum ada materi</h3>
            <p class="empty-state-text">
                Belum ada materi untuk kelas ini.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Tipe</th>
                            <th>Publikasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($materials as $material)
                            <tr>
                                <td>
                                    <div style="display: grid; gap: 6px;">
                                        <strong>{{ $material->title }}</strong>
                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ str($material->description)->limit(90) ?: '-' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="status-pill status-info">
                                        {{ strtoupper($material->type ?? 'Materi') }}
                                    </span>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $material->published_at ? $material->published_at->timezone($timezone)->format('d M Y H:i').' WIB' : '-' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="actions-inline">
                                        <a href="{{ route('assistant.materi.show', $material) }}" class="btn btn-sm">
                                            Detail
                                        </a>

                                        <a href="{{ route('assistant.materi.edit', $material) }}" class="btn btn-sm">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</section>

<section class="card" style="margin-bottom: 22px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Tugas</h2>
            <div class="section-subtitle">
                Daftar tugas yang sudah dibuat untuk kelas ini.
            </div>
        </div>

        <a href="{{ $createAssignmentUrl }}" class="btn btn-primary btn-sm">
            + Tugas
        </a>
    </div>

    @if($assignments->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">📝</div>
            <h3 class="empty-state-title">Belum ada tugas</h3>
            <p class="empty-state-text">
                Belum ada tugas untuk kelas ini.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Deadline</th>
                            <th>Submission</th>
                            <th>Nilai Maks</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($assignments as $assignment)
                            @php
                                $deadline = $assignment->deadline;
                                $isPastDeadline = $deadline && $deadline->isPast();
                            @endphp

                            <tr>
                                <td>
                                    <div style="display: grid; gap: 6px;">
                                        <strong>{{ $assignment->title }}</strong>
                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ str($assignment->description)->limit(90) ?: '-' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    @if($deadline)
                                        <div style="display: grid; gap: 6px;">
                                            <span class="item-meta" style="margin-top: 0;">
                                                {{ $deadline->timezone($timezone)->format('d M Y H:i').' WIB' }}
                                            </span>

                                            <span class="status-pill {{ $isPastDeadline ? 'status-danger' : 'status-warning' }}">
                                                {{ $isPastDeadline ? 'Deadline lewat' : 'Aktif' }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="status-pill status-muted">
                                            Belum diatur
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <span class="status-pill status-muted">
                                        {{ $assignment->submissions_count ?? 0 }} Submission
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill status-info">
                                        {{ $assignment->max_score }}
                                    </span>
                                </td>

                                <td>
                                    <div class="actions-inline">
                                        <a href="{{ route('assistant.tugas.show', $assignment) }}" class="btn btn-sm">
                                            Detail
                                        </a>

                                        <a href="{{ route('assistant.tugas.edit', $assignment) }}" class="btn btn-sm">
                                            Edit
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</section>

<section class="card" style="margin-bottom: 22px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Absensi</h2>
            <div class="section-subtitle">
                Daftar sesi absensi yang sudah dibuat untuk kelas ini.
            </div>
        </div>

        <a href="{{ $createAttendanceUrl }}" class="btn btn-primary btn-sm">
            + Absensi
        </a>
    </div>

    @if($attendances->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">✅</div>
            <h3 class="empty-state-title">Belum ada absensi</h3>
            <p class="empty-state-text">
                Belum ada absensi untuk kelas ini.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Status</th>
                            <th>Record</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($attendances as $attendance)
                            @php
                                $statusLabel = method_exists($attendance, 'statusLabel')
                                    ? $attendance->statusLabel()
                                    : ($attendance->is_open ? 'Sedang Dibuka' : 'Ditutup');

                                $rawStatusClass = method_exists($attendance, 'statusBadgeClass')
                                    ? $attendance->statusBadgeClass()
                                    : ($attendance->is_open ? 'badge-green' : 'badge-red');

                                $statusClass = match ($rawStatusClass) {
                                    'badge-green' => 'status-success',
                                    'badge-blue' => 'status-info',
                                    'badge-red' => 'status-danger',
                                    'badge-yellow' => 'status-warning',
                                    default => 'status-muted',
                                };
                            @endphp

                            <tr>
                                <td>
                                    <div class="item-meta" style="margin-top: 0;">
                                        <strong>Dibuka:</strong>
                                        {{ $attendance->opened_at ? $attendance->opened_at->timezone($timezone)->format('d M Y H:i').' WIB' : '-' }}
                                        <br>
                                        <strong>Ditutup:</strong>
                                        {{ $attendance->closed_at ? $attendance->closed_at->timezone($timezone)->format('d M Y H:i').' WIB' : '-' }}
                                    </div>
                                </td>

                                <td>
                                    <span class="status-pill {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill status-muted">
                                        {{ $attendance->records_count ?? 0 }} Record
                                    </span>
                                </td>

                                <td>
                                    <a href="{{ route('assistant.attendances.show', $attendance) }}" class="btn btn-sm">
                                        Kelola
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Submission Terbaru</h2>
            <div class="section-subtitle">
                Pengumpulan tugas terbaru dari mahasiswa pada kelas ini.
            </div>
        </div>

        @if(Route::has('assistant.submissions.index'))
            <a href="{{ route('assistant.submissions.index') }}" class="btn btn-sm">
                Semua Submission
            </a>
        @endif
    </div>

    @if($latestSubmissions->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">📥</div>
            <h3 class="empty-state-title">Belum ada submission</h3>
            <p class="empty-state-text">
                Belum ada submission untuk kelas ini.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Tugas</th>
                            <th>Dikumpulkan</th>
                            <th>Nilai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($latestSubmissions as $submission)
                            <tr>
                                <td>
                                    <strong>{{ $submission->student?->name ?? '-' }}</strong>
                                </td>

                                <td>
                                    {{ $submission->assignment?->title ?? '-' }}
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $submission->submitted_at ? $submission->submitted_at->timezone($timezone)->format('d M Y H:i').' WIB' : '-' }}
                                    </span>
                                </td>

                                <td>
                                    @if($submission->score !== null)
                                        <span class="status-pill status-success">
                                            {{ $submission->score }}
                                        </span>
                                    @else
                                        <span class="status-pill status-warning">
                                            Belum dinilai
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('assistant.submissions.show', $submission) }}" class="btn btn-sm btn-primary">
                                        Nilai
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</section>
@endsection