@extends('layouts.app')

@section('title', 'Kelola Kelas Praktikum')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $classes = $classes ?? collect();
    $studySemesters = $studySemesters ?? collect();
    $courses = $courses ?? collect();
    $assistants = $assistants ?? collect();

    $classTypes = $classTypes ?? [
        'regular' => 'Reguler',
        'combined' => 'Gabungan',
    ];

    $studentGroups = $studentGroups ?? ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

    $dashboardUrl = Route::has('admin.dashboard')
        ? route('admin.dashboard')
        : '#';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Kelola Kelas Praktikum</h1>

    <p>
        Kelola kelas praktikum berdasarkan semester, rombel, mata kuliah, jadwal,
        ruangan, tipe kelas, asisten, dan status aktif kelas.
    </p>

    <div class="hero-actions">
        <a href="{{ $dashboardUrl }}" class="btn">
            ← Dashboard
        </a>

        <a href="{{ route('admin.kelas.create') }}" class="btn btn-primary">
            + Tambah Kelas
        </a>
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Filter Kelas Praktikum</h2>
            <div class="section-subtitle">
                Cari kelas berdasarkan nama kelas, mata kuliah, asisten, semester, tipe kelas, rombel, atau status.
            </div>
        </div>
    </div>

    <form method="GET" class="actions-inline">
        <input
            class="form-control"
            style="width: 220px;"
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari kelas/matkul/asisten"
        >

        <select class="form-control" style="width: 180px;" name="study_semester_id">
            <option value="">Semua semester</option>

            @foreach($studySemesters as $semester)
                <option
                    value="{{ $semester->id }}"
                    @selected((string) request('study_semester_id') === (string) $semester->id)
                >
                    {{ $semester->name }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width: 220px;" name="course_id">
            <option value="">Semua mata kuliah</option>

            @foreach($courses as $course)
                <option
                    value="{{ $course->id }}"
                    @selected((string) request('course_id') === (string) $course->id)
                >
                    {{ $course->code }} - {{ $course->name }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width: 200px;" name="assistant_id">
            <option value="">Semua asisten</option>

            @foreach($assistants as $assistant)
                <option
                    value="{{ $assistant->id }}"
                    @selected((string) request('assistant_id') === (string) $assistant->id)
                >
                    {{ $assistant->name }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width: 150px;" name="class_type">
            <option value="">Semua tipe</option>

            @foreach($classTypes as $value => $label)
                <option value="{{ $value }}" @selected(request('class_type') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width: 150px;" name="student_group">
            <option value="">Semua rombel</option>

            @foreach($studentGroups as $group)
                <option value="{{ $group }}" @selected(request('student_group') === $group)>
                    Kelas {{ $group }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width: 150px;" name="status">
            <option value="">Semua status</option>

            <option value="1" @selected(request('status') === '1')>
                Aktif
            </option>

            <option value="0" @selected(request('status') === '0')>
                Nonaktif
            </option>
        </select>

        <button class="btn" type="submit">
            Filter
        </button>

        @if(request()->hasAny(['search', 'study_semester_id', 'course_id', 'assistant_id', 'class_type', 'student_group', 'status']))
            <a href="{{ route('admin.kelas.index') }}" class="btn">
                Reset
            </a>
        @endif
    </form>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Kelas Praktikum</h2>
            <div class="section-subtitle">
                Kelas praktikum menjadi ruang utama untuk materi, tugas, absensi, pengumuman, dan relasi mahasiswa.
            </div>
        </div>

        <a href="{{ route('admin.kelas.create') }}" class="btn btn-primary btn-sm">
            + Tambah Kelas
        </a>
    </div>

    @if($classes->count() === 0)
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">🏫</div>

            <h3 class="empty-state-title">
                Belum ada kelas praktikum
            </h3>

            <p class="empty-state-text">
                Data kelas praktikum akan tampil setelah admin menambahkan kelas.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            <th>Mata Kuliah</th>
                            <th>Semester</th>
                            <th>Tipe / Rombel</th>
                            <th>Asisten</th>
                            <th>Jadwal</th>
                            <th>Konten</th>
                            <th>Mahasiswa Otomatis</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($classes as $class)
                            @php
                                $classType = $class->class_type ?? 'regular';
                                $classTypeLabel = $class->class_type_label ?? ($classType === 'combined' ? 'Gabungan' : 'Reguler');
                                $groupDisplay = $class->group_display ?? 'Belum diatur';
                                $automaticStudentsCount = $class->automatic_students_count ?? 0;

                                $materialsCount = (int) ($class->materials_count ?? 0);
                                $assignmentsCount = (int) ($class->assignments_count ?? 0);
                                $attendancesCount = (int) ($class->attendances_count ?? 0);
                                $announcementsCount = (int) ($class->announcements_count ?? 0);

                                $canDelete = $materialsCount === 0
                                    && $assignmentsCount === 0
                                    && $attendancesCount === 0
                                    && $announcementsCount === 0;

                                $deleteReason = 'Kelas tidak bisa dihapus karena masih memiliki data terkait.';

                                if ($materialsCount > 0) {
                                    $deleteReason = 'Tidak bisa hapus karena kelas masih memiliki materi.';
                                } elseif ($assignmentsCount > 0) {
                                    $deleteReason = 'Tidak bisa hapus karena kelas masih memiliki tugas.';
                                } elseif ($attendancesCount > 0) {
                                    $deleteReason = 'Tidak bisa hapus karena kelas masih memiliki absensi.';
                                } elseif ($announcementsCount > 0) {
                                    $deleteReason = 'Tidak bisa hapus karena kelas masih memiliki pengumuman.';
                                }
                            @endphp

                            <tr>
                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $class->name }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            Ruang: {{ $class->room ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $class->course?->code ?? '-' }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ $class->course?->name ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $class->course?->studySemester?->name ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <div style="display: grid; gap: 6px;">
                                        <span class="status-pill {{ $classType === 'combined' ? 'status-warning' : 'status-success' }}">
                                            {{ $classTypeLabel }}
                                        </span>

                                        <span class="item-meta" style="margin-top: 0;">
                                            @if($classType === 'combined' && $class->group_label)
                                                {{ $class->group_label }}:
                                            @endif

                                            {{ $groupDisplay }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $class->assistant?->name ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $class->schedule ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <div style="display: grid; gap: 6px;">
                                        <span class="status-pill status-muted">
                                            Materi: {{ $materialsCount }}
                                        </span>

                                        <span class="status-pill status-muted">
                                            Tugas: {{ $assignmentsCount }}
                                        </span>

                                        <span class="status-pill status-muted">
                                            Absensi: {{ $attendancesCount }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <span class="status-pill status-info">
                                            {{ $automaticStudentsCount }} Mahasiswa
                                        </span>

                                        <span class="item-meta" style="margin-top: 0;">
                                            @if($classType === 'regular')
                                                Semester + rombel
                                            @else
                                                Semester + gabungan
                                            @endif
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="status-pill {{ $class->is_active ? 'status-success' : 'status-danger' }}">
                                        {{ $class->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="actions-inline">
                                        <a class="btn btn-sm" href="{{ route('admin.kelas.show', $class) }}">
                                            Detail
                                        </a>

                                        <a class="btn btn-sm" href="{{ route('admin.kelas.edit', $class) }}">
                                            Edit
                                        </a>

                                        @if($canDelete)
                                            @include('partials.delete-button', [
                                                'action' => route('admin.kelas.destroy', $class),
                                                'confirm' => 'Yakin ingin menghapus kelas ini?'
                                            ])
                                        @else
                                            <button
                                                type="button"
                                                class="btn btn-sm"
                                                style="cursor: not-allowed; opacity: .55;"
                                                disabled
                                                title="{{ $deleteReason }}"
                                            >
                                                Hapus
                                            </button>
                                        @endif
                                    </div>

                                    @unless($canDelete)
                                        <div class="item-meta" style="margin-top: 6px;">
                                            {{ $deleteReason }}
                                        </div>
                                    @endunless
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 18px;">
            {{ $classes->links() }}
        </div>
    @endif
</section>
@endsection