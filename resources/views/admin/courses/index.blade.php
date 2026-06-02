@extends('layouts.app')

@section('title', 'Kelola Mata Kuliah')

@section('content')

@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Kelola Mata Kuliah',
    'description' => 'Kelola mata kuliah berdasarkan semester mahasiswa dan tahun akademik.'
])

<div class="toolbar">
    <form method="GET" class="actions-inline">
        <input
            class="form-control"
            style="width:240px;"
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari nama/kode"
        >

        <select class="form-control" style="width:190px;" name="study_semester_id">
            <option value="">Semua semester</option>

            @foreach($studySemesters as $semester)
                <option value="{{ $semester->id }}" @selected((string) request('study_semester_id') === (string) $semester->id)>
                    {{ $semester->name }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width:210px;" name="academic_year_id">
            <option value="">Semua tahun akademik</option>

            @foreach($academicYears as $year)
                <option value="{{ $year->id }}" @selected((string) request('academic_year_id') === (string) $year->id)>
                    {{ $year->year }} - {{ ucfirst($year->semester) }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width:150px;" name="status">
            <option value="">Semua status</option>
            <option value="1" @selected(request('status') === '1')>Aktif</option>
            <option value="0" @selected(request('status') === '0')>Nonaktif</option>
        </select>

        <button class="btn" type="submit">Filter</button>

        @if(request()->hasAny(['search', 'study_semester_id', 'academic_year_id', 'status']))
            <a href="{{ route('admin.matakuliah.index') }}" class="btn">Reset</a>
        @endif
    </form>

    <a href="{{ route('admin.matakuliah.create') }}" class="btn btn-primary">
        + Tambah Mata Kuliah
    </a>
</div>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Mata Kuliah</th>
                <th>Semester Mahasiswa</th>
                <th>Tahun Akademik</th>
                <th>SKS</th>
                <th>Kelas</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($courses as $course)
                <tr>
                    <td>
                        <strong>{{ $course->code }}</strong>
                    </td>

                    <td>
                        <strong>{{ $course->name }}</strong>
                    </td>

                    <td>
                        {{ $course->studySemester?->name ?? '-' }}
                    </td>

                    <td>
                        {{ $course->academicYear?->year ?? '-' }}

                        @if($course->academicYear)
                            - {{ ucfirst($course->academicYear->semester) }}
                        @endif
                    </td>

                    <td>
                        {{ $course->sks }}
                    </td>

                    <td>
                        {{ $course->classes_count }}
                    </td>

                    <td>
                        <span class="badge {{ $course->is_active ? 'badge-green' : 'badge-red' }}">
                            {{ $course->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>

                    <td class="actions-inline">
                        <a class="btn btn-sm" href="{{ route('admin.matakuliah.show', $course) }}">
                            Detail
                        </a>

                        <a class="btn btn-sm" href="{{ route('admin.matakuliah.edit', $course) }}">
                            Edit
                        </a>

                        @include('partials.delete-button', [
                            'action' => route('admin.matakuliah.destroy', $course),
                            'confirm' => 'Yakin ingin menghapus mata kuliah ini?'
                        ])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        Belum ada mata kuliah.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">
    {{ $courses->links() }}
</div>

@endsection