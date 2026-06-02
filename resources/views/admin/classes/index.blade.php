@extends('layouts.app')

@section('title', 'Kelola Kelas Praktikum')

@section('content')
@php
    $classTypes = $classTypes ?? [
        'regular' => 'Reguler',
        'combined' => 'Gabungan',
    ];

    $studentGroups = $studentGroups ?? ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
@endphp

@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Kelola Kelas Praktikum',
    'description' => 'Kelola kelas praktikum berdasarkan semester, rombel, mata kuliah, jadwal, ruangan, dan asisten.'
])

<div class="toolbar">
    <form method="GET" class="actions-inline">
        <input
            class="form-control"
            style="width:220px;"
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari kelas/matkul/asisten"
        >

        <select class="form-control" style="width:180px;" name="study_semester_id">
            <option value="">Semua semester</option>
            @foreach($studySemesters as $semester)
                <option value="{{ $semester->id }}" @selected((string) request('study_semester_id') === (string) $semester->id)>
                    {{ $semester->name }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width:220px;" name="course_id">
            <option value="">Semua mata kuliah</option>
            @foreach($courses as $course)
                <option value="{{ $course->id }}" @selected((string) request('course_id') === (string) $course->id)>
                    {{ $course->code }} - {{ $course->name }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width:200px;" name="assistant_id">
            <option value="">Semua asisten</option>
            @foreach($assistants as $assistant)
                <option value="{{ $assistant->id }}" @selected((string) request('assistant_id') === (string) $assistant->id)>
                    {{ $assistant->name }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width:150px;" name="class_type">
            <option value="">Semua tipe</option>
            @foreach($classTypes as $value => $label)
                <option value="{{ $value }}" @selected(request('class_type') === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width:150px;" name="student_group">
            <option value="">Semua rombel</option>
            @foreach($studentGroups as $group)
                <option value="{{ $group }}" @selected(request('student_group') === $group)>
                    Kelas {{ $group }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width:150px;" name="status">
            <option value="">Semua status</option>
            <option value="1" @selected(request('status') === '1')>Aktif</option>
            <option value="0" @selected(request('status') === '0')>Nonaktif</option>
        </select>

        <button class="btn" type="submit">Filter</button>

        @if(request()->hasAny(['search', 'study_semester_id', 'course_id', 'assistant_id', 'class_type', 'student_group', 'status']))
            <a href="{{ route('admin.kelas.index') }}" class="btn">Reset</a>
        @endif
    </form>

    <a href="{{ route('admin.kelas.create') }}" class="btn btn-primary">
        + Tambah Kelas
    </a>
</div>

<div class="table-card">
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
            @forelse($classes as $class)
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
                        <strong>{{ $class->name }}</strong>
                        <br>
                        <small>{{ $class->room ?? '-' }}</small>
                    </td>

                    <td>
                        <strong>{{ $class->course?->code ?? '-' }}</strong>
                        <br>
                        <small>{{ $class->course?->name ?? '-' }}</small>
                    </td>

                    <td>
                        {{ $class->course?->studySemester?->name ?? '-' }}
                    </td>

                    <td>
                        <span class="badge {{ $classType === 'combined' ? 'badge-red' : 'badge-green' }}">
                            {{ $classTypeLabel }}
                        </span>

                        <br>

                        <small>
                            @if($classType === 'combined' && $class->group_label)
                                {{ $class->group_label }}:
                            @endif

                            {{ $groupDisplay }}
                        </small>
                    </td>

                    <td>
                        {{ $class->assistant?->name ?? '-' }}
                    </td>

                    <td>
                        {{ $class->schedule ?? '-' }}
                    </td>

                    <td>
                        <small>
                            Materi: {{ $materialsCount }}<br>
                            Tugas: {{ $assignmentsCount }}<br>
                            Absensi: {{ $attendancesCount }}
                        </small>
                    </td>

                    <td>
                        <strong>{{ $automaticStudentsCount }}</strong>
                        <br>
                        <small>
                            @if($classType === 'regular')
                                Semester + rombel
                            @else
                                Semester + gabungan
                            @endif
                        </small>
                    </td>

                    <td>
                        <span class="badge {{ $class->is_active ? 'badge-green' : 'badge-red' }}">
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
                                    style="cursor:not-allowed; opacity:.55;"
                                    disabled
                                    title="{{ $deleteReason }}"
                                >
                                    Hapus
                                </button>
                            @endif
                        </div>

                        @unless($canDelete)
                            <small class="mt-1 block text-slate-400">
                                {{ $deleteReason }}
                            </small>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10">
                        Belum ada kelas praktikum.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">
    {{ $classes->links() }}
</div>
@endsection