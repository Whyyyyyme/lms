@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $safe = fn (string $name, array $params = []) => Route::has($name)
        ? route($name, $params)
        : '#';

    $statistics = $statistics ?? [];
    $semesterSummaries = $semesterSummaries ?? collect();
    $latestUsers = $latestUsers ?? collect();
    $latestClasses = $latestClasses ?? collect();
    $latestSubmissions = $latestSubmissions ?? collect();

    $needFixStudentSemester = ($statistics['total_mahasiswa_tanpa_semester'] ?? 0) > 0;
    $needFixClassAssistant = ($statistics['total_classes_without_assistant'] ?? 0) > 0;
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Dashboard Admin</h1>

    <p>
        Ringkasan data LMS Praktikum berdasarkan semester, mata kuliah, kelas, user,
        tugas, submission, dan absensi.
    </p>

    <div class="hero-actions">
        <a href="{{ $safe('admin.users.create') }}" class="btn btn-primary">
            👥 Tambah User
        </a>

        <a href="{{ $safe('admin.matakuliah.create') }}" class="btn">
            📚 Tambah Mata Kuliah
        </a>

        <a href="{{ $safe('admin.kelas.create') }}" class="btn">
            🏫 Tambah Kelas
        </a>
    </div>
</section>

<div class="grid grid-4" style="margin-bottom: 18px;">
    <div class="stat-card">
        <div class="stat-label">Asisten</div>
        <div class="stat-value">{{ $statistics['total_asisten'] ?? 0 }}</div>
        <div class="stat-note">Total akun asisten praktikum.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Mahasiswa</div>
        <div class="stat-value">{{ $statistics['total_mahasiswa'] ?? 0 }}</div>
        <div class="stat-note">Total akun mahasiswa.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Mata Kuliah</div>
        <div class="stat-value">{{ $statistics['total_courses'] ?? 0 }}</div>
        <div class="stat-note">Total mata kuliah praktikum.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Kelas Praktikum</div>
        <div class="stat-value">{{ $statistics['total_classes'] ?? 0 }}</div>
        <div class="stat-note">Total kelas praktikum.</div>
    </div>
</div>

<div class="grid grid-4" style="margin-bottom: 18px;">
    <div class="stat-card">
        <div class="stat-label">Semester Aktif</div>
        <div class="stat-value" style="font-size: 28px;">
            {{ ($statistics['total_active_semesters'] ?? 0) . ' / ' . ($statistics['total_semesters'] ?? 0) }}
        </div>
        <div class="stat-note">Semester aktif dibanding total semester.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Kelas Aktif</div>
        <div class="stat-value" style="font-size: 28px;">
            {{ ($statistics['total_active_classes'] ?? 0) . ' / ' . ($statistics['total_classes'] ?? 0) }}
        </div>
        <div class="stat-note">Kelas aktif dibanding total kelas.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Submission Belum Dinilai</div>
        <div class="stat-value">{{ $statistics['total_ungraded_submissions'] ?? 0 }}</div>
        <div class="stat-note">Submission yang perlu dicek asisten.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Absensi Terbuka</div>
        <div class="stat-value">{{ $statistics['total_open_attendances'] ?? 0 }}</div>
        <div class="stat-note">Sesi absensi yang sedang dibuka.</div>
    </div>
</div>

@if($needFixStudentSemester || $needFixClassAssistant)
    <section class="card" style="margin-bottom: 18px; border-color: #fecaca;">
        <div class="section-header">
            <div>
                <h2 class="section-title">Data Perlu Diperbaiki</h2>
                <div class="section-subtitle">
                    Beberapa data utama belum lengkap dan dapat memengaruhi akses mahasiswa atau asisten.
                </div>
            </div>

            <span class="status-pill status-danger">
                Perlu Dicek
            </span>
        </div>

        <div class="list-stack">
            @if($needFixStudentSemester)
                <div class="list-item">
                    <div>
                        <h3 class="item-title">Mahasiswa belum punya semester</h3>
                        <div class="item-meta">
                            Ada {{ $statistics['total_mahasiswa_tanpa_semester'] }} mahasiswa yang belum memiliki semester.
                            Cek menu Kelola Asisten & Mahasiswa.
                        </div>
                    </div>

                    <a href="{{ $safe('admin.users.index') }}" class="btn btn-sm">
                        Cek User
                    </a>
                </div>
            @endif

            @if($needFixClassAssistant)
                <div class="list-item">
                    <div>
                        <h3 class="item-title">Kelas belum punya asisten</h3>
                        <div class="item-meta">
                            Ada {{ $statistics['total_classes_without_assistant'] }} kelas praktikum yang belum memiliki asisten.
                            Cek menu Kelola Kelas Praktikum.
                        </div>
                    </div>

                    <a href="{{ $safe('admin.kelas.index') }}" class="btn btn-sm">
                        Cek Kelas
                    </a>
                </div>
            @endif
        </div>
    </section>
@endif

<section class="card" style="margin-bottom: 22px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Aksi Cepat Admin</h2>
            <div class="section-subtitle">
                Gunakan menu berikut untuk mengelola data utama LMS Praktikum.
            </div>
        </div>
    </div>

    <div class="grid grid-3">
        <a href="{{ $safe('admin.users.create') }}" class="action-card card">
            <div class="metric-pill">👥 User</div>

            <h3 class="course-title">Tambah Asisten / Mahasiswa</h3>

            <p class="course-meta">
                Tambah akun asisten praktikum atau mahasiswa. Akun admin utama dikelola manual.
            </p>

            <div class="course-footer">
                <span class="status-pill status-info">Tambah user</span>
                <span style="font-weight: 900; color: var(--primary);">→</span>
            </div>
        </a>

        <a href="{{ $safe('admin.semester.create') }}" class="action-card card">
            <div class="metric-pill">🎓 Semester</div>

            <h3 class="course-title">Tambah Semester</h3>

            <p class="course-meta">
                Buat semester mahasiswa untuk dasar mata kuliah dan kelas praktikum.
            </p>

            <div class="course-footer">
                <span class="status-pill status-info">Tambah semester</span>
                <span style="font-weight: 900; color: var(--primary);">→</span>
            </div>
        </a>

        <a href="{{ $safe('admin.matakuliah.create') }}" class="action-card card">
            <div class="metric-pill">📚 Mata Kuliah</div>

            <h3 class="course-title">Tambah Mata Kuliah</h3>

            <p class="course-meta">
                Buat mata kuliah dan hubungkan ke semester mahasiswa.
            </p>

            <div class="course-footer">
                <span class="status-pill status-info">Tambah mata kuliah</span>
                <span style="font-weight: 900; color: var(--primary);">→</span>
            </div>
        </a>

        <a href="{{ $safe('admin.kelas.create') }}" class="action-card card">
            <div class="metric-pill">🏫 Kelas</div>

            <h3 class="course-title">Tambah Kelas</h3>

            <p class="course-meta">
                Buat kelas praktikum, pilih mata kuliah, dan hubungkan asisten.
            </p>

            <div class="course-footer">
                <span class="status-pill status-info">Tambah kelas</span>
                <span style="font-weight: 900; color: var(--primary);">→</span>
            </div>
        </a>

        <a href="{{ $safe('admin.reports.scores') }}" class="action-card card">
            <div class="metric-pill">🧾 Nilai</div>

            <h3 class="course-title">Laporan Nilai</h3>

            <p class="course-meta">
                Lihat rekap nilai submission mahasiswa.
            </p>

            <div class="course-footer">
                <span class="status-pill status-info">Buka laporan</span>
                <span style="font-weight: 900; color: var(--primary);">→</span>
            </div>
        </a>

        <a href="{{ $safe('admin.reports.attendances') }}" class="action-card card">
            <div class="metric-pill">✅ Absensi</div>

            <h3 class="course-title">Laporan Absensi</h3>

            <p class="course-meta">
                Pantau rekap kehadiran mahasiswa.
            </p>

            <div class="course-footer">
                <span class="status-pill status-info">Buka laporan</span>
                <span style="font-weight: 900; color: var(--primary);">→</span>
            </div>
        </a>
    </div>
</section>

<div class="admin-dashboard-grid">
    <section class="card">
        <div class="section-header">
            <div>
                <h2 class="section-title">Ringkasan Semester</h2>
                <div class="section-subtitle">
                    Data semester, jumlah mata kuliah, mahasiswa, dan status aktif.
                </div>
            </div>

            <a href="{{ $safe('admin.semester.index') }}" class="btn btn-sm">
                Semua Semester
            </a>
        </div>

        @if($semesterSummaries->isEmpty())
            <div class="empty-state">
                <div style="font-size: 34px; margin-bottom: 8px;">🎓</div>
                <h3 class="empty-state-title">Belum ada data semester</h3>
                <p class="empty-state-text">
                    Data semester akan tampil setelah admin membuat semester mahasiswa.
                </p>
            </div>
        @else
            <div class="table-card">
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Semester</th>
                                <th>Mata Kuliah</th>
                                <th>Mahasiswa</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($semesterSummaries as $semester)
                                <tr>
                                    <td>
                                        <div style="display: grid; gap: 5px;">
                                            <a href="{{ $safe('admin.semester.show', [$semester]) }}">
                                                <strong>{{ $semester->name }}</strong>
                                            </a>

                                            <span class="item-meta" style="margin-top: 0;">
                                                Level {{ $semester->level }}
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="status-pill status-muted">
                                            {{ $semester->courses_count }} MK
                                        </span>
                                    </td>

                                    <td>
                                        <span class="status-pill status-muted">
                                            {{ $semester->students_count }} Mahasiswa
                                        </span>
                                    </td>

                                    <td>
                                        <span class="status-pill {{ $semester->is_active ? 'status-success' : 'status-danger' }}">
                                            {{ $semester->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
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
                <h2 class="section-title">User Terbaru</h2>
                <div class="section-subtitle">
                    Daftar user terbaru yang terdaftar di sistem.
                </div>
            </div>

            <a href="{{ $safe('admin.users.index') }}" class="btn btn-sm">
                Semua User
            </a>
        </div>

        @if($latestUsers->isEmpty())
            <div class="empty-state">
                <div style="font-size: 34px; margin-bottom: 8px;">👥</div>
                <h3 class="empty-state-title">Belum ada user terbaru</h3>
                <p class="empty-state-text">
                    User terbaru akan tampil setelah data asisten atau mahasiswa dibuat.
                </p>
            </div>
        @else
            <div class="table-card">
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>User Terbaru</th>
                                <th>Jenis Akun</th>
                                <th>Semester</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($latestUsers as $user)
                                @php
                                    $currentRole = $user->roles->pluck('name')->first() ?: $user->role;

                                    $roleLabel = match ($currentRole) {
                                        'asisten' => 'Asisten',
                                        'mahasiswa' => 'Mahasiswa',
                                        'admin' => 'Admin',
                                        default => ucfirst($currentRole ?? '-'),
                                    };

                                    $roleClass = match ($currentRole) {
                                        'asisten' => 'status-info',
                                        'mahasiswa' => 'status-success',
                                        'admin' => 'status-warning',
                                        default => 'status-muted',
                                    };
                                @endphp

                                <tr>
                                    <td>
                                        <div style="display: grid; gap: 5px;">
                                            <a href="{{ $safe('admin.users.show', [$user]) }}">
                                                <strong>{{ $user->name }}</strong>
                                            </a>

                                            <span class="item-meta" style="margin-top: 0;">
                                                {{ $user->email }}
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="status-pill {{ $roleClass }}">
                                            {{ $roleLabel }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ $currentRole === 'mahasiswa' ? ($user->studySemester?->name ?? '-') : '-' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>
</div>

<div class="admin-dashboard-grid" style="margin-top: 22px;">
    <section class="card">
        <div class="section-header">
            <div>
                <h2 class="section-title">Kelas Terbaru</h2>
                <div class="section-subtitle">
                    Data kelas praktikum terbaru beserta mata kuliah, asisten, dan statusnya.
                </div>
            </div>

            <a href="{{ $safe('admin.kelas.index') }}" class="btn btn-sm">
                Semua Kelas
            </a>
        </div>

        @if($latestClasses->isEmpty())
            <div class="empty-state">
                <div style="font-size: 34px; margin-bottom: 8px;">🏫</div>
                <h3 class="empty-state-title">Belum ada kelas praktikum</h3>
                <p class="empty-state-text">
                    Kelas praktikum akan tampil setelah admin membuat data kelas.
                </p>
            </div>
        @else
            <div class="table-card">
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Kelas Terbaru</th>
                                <th>Mata Kuliah</th>
                                <th>Asisten</th>
                                <th>Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($latestClasses as $class)
                                <tr>
                                    <td>
                                        <div style="display: grid; gap: 5px;">
                                            <a href="{{ $safe('admin.kelas.show', [$class]) }}">
                                                <strong>{{ $class->name }}</strong>
                                            </a>

                                            <span class="item-meta" style="margin-top: 0;">
                                                {{ $class->course?->studySemester?->name ?? '-' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <div style="display: grid; gap: 5px;">
                                            <strong>{{ $class->course?->code ?? '-' }}</strong>

                                            <span class="item-meta" style="margin-top: 0;">
                                                {{ $class->course?->name ?? '-' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ $class->assistant?->name ?? '-' }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="status-pill {{ $class->is_active ? 'status-success' : 'status-danger' }}">
                                            {{ $class->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
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
                    Pengumpulan tugas terbaru dan status penilaiannya.
                </div>
            </div>

            <a href="{{ $safe('admin.reports.scores') }}" class="btn btn-sm">
                Laporan Nilai
            </a>
        </div>

        @if($latestSubmissions->isEmpty())
            <div class="empty-state">
                <div style="font-size: 34px; margin-bottom: 8px;">📥</div>
                <h3 class="empty-state-title">Belum ada submission</h3>
                <p class="empty-state-text">
                    Submission terbaru akan tampil setelah mahasiswa mengumpulkan tugas.
                </p>
            </div>
        @else
            <div class="table-card">
                <div class="table-scroll">
                    <table>
                        <thead>
                            <tr>
                                <th>Submission Terbaru</th>
                                <th>Tugas</th>
                                <th>Status Nilai</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($latestSubmissions as $submission)
                                <tr>
                                    <td>
                                        <div style="display: grid; gap: 5px;">
                                            <strong>{{ $submission->student?->name ?? '-' }}</strong>

                                            <span class="item-meta" style="margin-top: 0;">
                                                {{ $submission->submitted_at?->format('d M Y H:i') ?? '-' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        <div style="display: grid; gap: 5px;">
                                            <strong>{{ $submission->assignment?->title ?? '-' }}</strong>

                                            <span class="item-meta" style="margin-top: 0;">
                                                {{ $submission->assignment?->kelas?->course?->name ?? '-' }}
                                            </span>
                                        </div>
                                    </td>

                                    <td>
                                        @if($submission->graded_at)
                                            <span class="status-pill status-success">
                                                Sudah dinilai
                                            </span>
                                        @else
                                            <span class="status-pill status-danger">
                                                Belum dinilai
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>
</div>

<style>
    .admin-dashboard-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 22px;
        align-items: start;
    }

    @media (max-width: 1100px) {
        .admin-dashboard-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection