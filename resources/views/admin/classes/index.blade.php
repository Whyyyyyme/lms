@extends('layouts.app')

@section('title', 'Kelola Kelas Praktikum')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Kelola Kelas Praktikum',
    'description' => 'Kelola kelas, jadwal, ruangan, asisten, dan pembagian mahasiswa khusus.'
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

        <select class="form-control" style="width:150px;" name="status">
            <option value="">Semua status</option>
            <option value="1" @selected(request('status') === '1')>Aktif</option>
            <option value="0" @selected(request('status') === '0')>Nonaktif</option>
        </select>

        <button class="btn" type="submit">Filter</button>

        @if(request()->hasAny(['search', 'study_semester_id', 'course_id', 'assistant_id', 'status']))
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
                <th>Asisten</th>
                <th>Jadwal</th>
                <th>Konten</th>
                <th>Mahasiswa</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($classes as $class)
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
                        {{ $class->assistant?->name ?? '-' }}
                    </td>

                    <td>
                        {{ $class->schedule ?? '-' }}
                    </td>

                    <td>
                        <small>
                            Materi: {{ $class->materials_count }}<br>
                            Tugas: {{ $class->assignments_count }}<br>
                            Absensi: {{ $class->attendances_count }}
                        </small>
                    </td>

                    <td>
                        <strong>{{ $class->resolved_students_count ?? 0 }}</strong>
                        <br>
                        <small>Manual: {{ $class->students_count }}</small>
                    </td>

                    <td>
                        <span class="badge {{ $class->is_active ? 'badge-green' : 'badge-red' }}">
                            {{ $class->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>

                    <td class="actions-inline">
                        <a class="btn btn-sm" href="{{ route('admin.kelas.show', $class) }}">
                            Detail
                        </a>

                        <a class="btn btn-sm" href="{{ route('admin.kelas.edit', $class) }}">
                            Edit
                        </a>

                        @if($class->materials_count === 0 && $class->assignments_count === 0 && $class->attendances_count === 0)
                            @include('partials.delete-button', [
                                'action' => route('admin.kelas.destroy', $class),
                                'confirm' => 'Yakin ingin menghapus kelas ini?'
                            ])
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">
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