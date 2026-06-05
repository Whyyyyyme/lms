@extends('layouts.app')

@section('title', 'Detail Kelas Praktikum')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $safe = fn (string $name, array $params = []) => Route::has($name)
        ? route($name, $params)
        : '#';

    $automaticStudents = $automaticStudents ?? collect();
    $resolvedStudents = $resolvedStudents ?? collect();

    $materials = $praktikumClass->materials ?? collect();
    $assignments = $praktikumClass->assignments ?? collect();

    $classType = $praktikumClass->class_type ?? 'regular';

    $classTypeLabel = match ($classType) {
        'combined' => 'Gabungan',
        default => 'Reguler',
    };

    $groupMembers = collect($praktikumClass->group_members ?? [])
        ->filter()
        ->values();

    $groupDisplay = '-';

    if ($classType === 'regular') {
        $groupDisplay = $praktikumClass->student_group
            ? 'Kelas ' . $praktikumClass->student_group
            : '-';
    }

    if ($classType === 'combined') {
        $groupDisplay = $groupMembers->isNotEmpty()
            ? $groupMembers->map(fn ($group) => 'Kelas ' . $group)->implode(', ')
            : '-';
    }

    $materialsCount = (int) ($praktikumClass->materials_count ?? $materials->count());
    $assignmentsCount = (int) ($praktikumClass->assignments_count ?? $assignments->count());
    $attendancesCount = (int) ($praktikumClass->attendances_count ?? 0);
    $announcementsCount = (int) ($praktikumClass->announcements_count ?? 0);
    $manualStudentsCount = (int) ($praktikumClass->students_count ?? $praktikumClass->students?->count() ?? 0);

    $canDelete = $materialsCount === 0
        && $assignmentsCount === 0
        && $attendancesCount === 0
        && $announcementsCount === 0;

    $backUrl = Route::has('admin.kelas.index')
        ? route('admin.kelas.index')
        : (Route::has('admin.dashboard') ? route('admin.dashboard') : '#');

    $academicYearText = $praktikumClass->course?->academicYear
        ? $praktikumClass->course->academicYear->year . ' - ' . ucfirst($praktikumClass->course->academicYear->semester)
        : '-';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Detail Kelas Praktikum</h1>

    <p>
        Detail kelas, mata kuliah, asisten, mahasiswa otomatis, mahasiswa manual,
        materi, tugas, absensi, dan pengumuman kelas praktikum.
    </p>

    <div class="hero-actions">
        <a href="{{ $backUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('admin.kelas.edit'))
            <a href="{{ route('admin.kelas.edit', $praktikumClass) }}" class="btn btn-primary">
                Edit Kelas
            </a>
        @endif
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">
                {{ $praktikumClass->name }}
            </h2>

            <div class="section-subtitle">
                Ringkasan informasi utama kelas praktikum.
            </div>
        </div>

        <span class="status-pill {{ $praktikumClass->is_active ? 'status-success' : 'status-danger' }}">
            {{ $praktikumClass->is_active ? 'Aktif' : 'Nonaktif' }}
        </span>
    </div>

    <div class="grid grid-4">
        <div class="stat-card">
            <div class="stat-label">Kelas</div>
            <div class="stat-value" style="font-size: 22px;">
                {{ $praktikumClass->name }}
            </div>
            <div class="stat-note">
                Nama kelas praktikum.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Status</div>
            <div class="stat-value" style="font-size: 22px;">
                {{ $praktikumClass->is_active ? 'Aktif' : 'Nonaktif' }}
            </div>
            <div class="stat-note">
                Status penggunaan kelas.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Ruangan</div>
            <div class="stat-value" style="font-size: 22px;">
                {{ $praktikumClass->room ?? '-' }}
            </div>
            <div class="stat-note">
                Ruang praktikum.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Jadwal</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $praktikumClass->schedule ?? '-' }}
            </div>
            <div class="stat-note">
                Jadwal kelas praktikum.
            </div>
        </div>
    </div>

    <div class="grid grid-4" style="margin-top: 18px;">
        <div class="stat-card">
            <div class="stat-label">Mata Kuliah</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $praktikumClass->course?->code ?? '-' }}
            </div>
            <div class="stat-note">
                {{ $praktikumClass->course?->name ?? 'Mata kuliah tidak ditemukan' }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Semester</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $praktikumClass->course?->studySemester?->name ?? '-' }}
            </div>
            <div class="stat-note">
                Semester mahasiswa dari mata kuliah.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Tahun Akademik</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $academicYearText }}
            </div>
            <div class="stat-note">
                Periode penyelenggaraan.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Asisten</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $praktikumClass->assistant?->name ?? 'Belum ditentukan' }}
            </div>
            <div class="stat-note">
                Asisten pengampu kelas.
            </div>
        </div>
    </div>

    <div class="grid grid-4" style="margin-top: 18px;">
        <div class="stat-card">
            <div class="stat-label">Tipe Kelas</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $classTypeLabel }}
            </div>
            <div class="stat-note">
                {{ $classType === 'combined' ? 'Kelas gabungan beberapa rombel.' : 'Kelas reguler satu rombel.' }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Rombel</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $groupDisplay }}
            </div>
            <div class="stat-note">
                Rombel yang masuk kelas ini.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Label Gabungan</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $praktikumClass->group_label ?? '-' }}
            </div>
            <div class="stat-note">
                Label khusus untuk kelas gabungan.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Mahasiswa Otomatis</div>
            <div class="stat-value">
                {{ $automaticStudents->count() }}
            </div>
            <div class="stat-note">
                Berdasarkan semester dan rombel.
            </div>
        </div>
    </div>

    <div class="grid grid-4" style="margin-top: 18px;">
        <div class="stat-card">
            <div class="stat-label">Mahasiswa Manual</div>
            <div class="stat-value">
                {{ $manualStudentsCount }}
            </div>
            <div class="stat-note">
                Mahasiswa tambahan khusus.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Materi</div>
            <div class="stat-value">
                {{ $materialsCount }}
            </div>
            <div class="stat-note">
                Total materi pada kelas ini.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Tugas</div>
            <div class="stat-value">
                {{ $assignmentsCount }}
            </div>
            <div class="stat-note">
                Total tugas pada kelas ini.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Absensi</div>
            <div class="stat-value">
                {{ $attendancesCount }}
            </div>
            <div class="stat-note">
                Total sesi absensi kelas.
            </div>
        </div>
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Mahasiswa Otomatis Berdasarkan Semester + Rombel</h2>
            <div class="section-subtitle">
                Daftar ini dihitung otomatis dari semester mata kuliah dan rombel kelas praktikum.
                Untuk kelas reguler, sistem mengambil mahasiswa sesuai satu rombel.
                Untuk kelas gabungan, sistem mengambil mahasiswa dari rombel yang digabung.
            </div>
        </div>

        <span class="status-pill status-info">
            {{ $automaticStudents->count() }} Mahasiswa
        </span>
    </div>

    @if($automaticStudents->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">🎓</div>

            <h3 class="empty-state-title">
                Belum ada mahasiswa otomatis
            </h3>

            <p class="empty-state-text">
                Belum ada mahasiswa otomatis untuk semester dan rombel kelas ini.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>NIM</th>
                            <th>Semester</th>
                            <th>Rombel</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($automaticStudents as $student)
                            <tr>
                                <td>
                                    <strong>{{ $student->name }}</strong>
                                </td>

                                <td>{{ $student->nim_nip ?? '-' }}</td>

                                <td>{{ $student->studySemester?->name ?? '-' }}</td>

                                <td>
                                    <span class="status-pill status-muted">
                                        {{ $student->student_group ? 'Kelas ' . $student->student_group : '-' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $student->email }}
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill {{ $student->is_active ? 'status-success' : 'status-danger' }}">
                                        {{ $student->is_active ? 'Aktif' : 'Nonaktif' }}
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

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Mahasiswa Manual / Khusus</h2>
            <div class="section-subtitle">
                Daftar ini hanya untuk tambahan khusus. Akses utama mahasiswa seharusnya dihitung otomatis dari semester dan rombel.
            </div>
        </div>

        <span class="status-pill status-muted">
            {{ $resolvedStudents->count() }} Mahasiswa
        </span>
    </div>

    @if($resolvedStudents->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">🎓</div>

            <h3 class="empty-state-title">
                Tidak ada mahasiswa manual
            </h3>

            <p class="empty-state-text">
                Mahasiswa otomatis tetap bisa mengakses kelas ini jika semester dan rombel mereka sesuai.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Mahasiswa yang Bisa Mengakses</th>
                            <th>NIM</th>
                            <th>Semester</th>
                            <th>Rombel</th>
                            <th>Email</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($resolvedStudents as $student)
                            <tr>
                                <td>
                                    <strong>{{ $student->name }}</strong>
                                </td>

                                <td>{{ $student->nim_nip ?? '-' }}</td>

                                <td>{{ $student->studySemester?->name ?? '-' }}</td>

                                <td>
                                    <span class="status-pill status-muted">
                                        {{ $student->student_group ? 'Kelas ' . $student->student_group : '-' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $student->email }}
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill {{ $student->is_active ? 'status-success' : 'status-danger' }}">
                                        {{ $student->is_active ? 'Aktif' : 'Nonaktif' }}
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

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Materi Kelas</h2>
            <div class="section-subtitle">
                Daftar materi yang sudah dibuat untuk kelas praktikum ini.
            </div>
        </div>

        <span class="status-pill status-muted">
            {{ $materialsCount }} Materi
        </span>
    </div>

    @if($materials->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">📘</div>

            <h3 class="empty-state-title">
                Belum ada materi
            </h3>

            <p class="empty-state-text">
                Belum ada materi pada kelas ini.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Materi</th>
                            <th>Tipe</th>
                            <th>Dibuat Oleh</th>
                            <th>Publikasi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($materials as $material)
                            <tr>
                                <td>
                                    <strong>{{ $material->title }}</strong>
                                </td>

                                <td>
                                    <span class="status-pill status-info">
                                        {{ strtoupper($material->type ?? '-') }}
                                    </span>
                                </td>

                                <td>{{ $material->creator?->name ?? '-' }}</td>

                                <td>{{ $material->published_at?->format('d M Y H:i') ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Tugas Kelas</h2>
            <div class="section-subtitle">
                Daftar tugas yang sudah dibuat untuk kelas praktikum ini.
            </div>
        </div>

        <span class="status-pill status-muted">
            {{ $assignmentsCount }} Tugas
        </span>
    </div>

    @if($assignments->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">📝</div>

            <h3 class="empty-state-title">
                Belum ada tugas
            </h3>

            <p class="empty-state-text">
                Belum ada tugas pada kelas ini.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Tugas</th>
                            <th>Deadline</th>
                            <th>Nilai Maksimal</th>
                            <th>Dibuat Oleh</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($assignments as $assignment)
                            <tr>
                                <td>
                                    <strong>{{ $assignment->title }}</strong>
                                </td>

                                <td>{{ $assignment->deadline?->format('d M Y H:i') ?? '-' }}</td>

                                <td>
                                    <span class="status-pill status-info">
                                        {{ $assignment->max_score ?? '-' }}
                                    </span>
                                </td>

                                <td>{{ $assignment->creator?->name ?? '-' }}</td>
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
            <h2 class="section-title">Aksi Kelas</h2>
            <div class="section-subtitle">
                Kembali ke daftar kelas, edit data kelas, atau hapus jika belum memiliki data terkait.
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a class="btn" href="{{ $backUrl }}">
            Kembali
        </a>

        @if(Route::has('admin.kelas.edit'))
            <a class="btn btn-primary" href="{{ route('admin.kelas.edit', $praktikumClass) }}">
                Edit Kelas
            </a>
        @endif

        @if($canDelete && Route::has('admin.kelas.destroy'))
            @include('partials.delete-button', [
                'action' => route('admin.kelas.destroy', $praktikumClass),
                'confirm' => 'Yakin ingin menghapus kelas ini?'
            ])
        @endif
    </div>
</section>
@endsection