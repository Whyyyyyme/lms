@extends('layouts.app')

@section('title', 'Kelola Semester Mahasiswa')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Kelola Semester Mahasiswa',
    'description' => 'Semester menjadi dasar pengelompokan mahasiswa dan mata kuliah praktikum.'
])

<div class="toolbar">
    <form method="GET" class="actions-inline">
        <input
            class="form-control"
            style="width:260px;"
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari semester"
        >

        <button class="btn" type="submit">Filter</button>

        @if(request()->filled('search'))
            <a href="{{ route('admin.semester.index') }}" class="btn">Reset</a>
        @endif
    </form>

    <a href="{{ route('admin.semester.create') }}" class="btn btn-primary">
        + Tambah Semester
    </a>
</div>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>Level</th>
                <th>Nama Semester</th>
                <th>Mata Kuliah</th>
                <th>Mahasiswa</th>
                <th>Riwayat Enrollment</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($studySemesters as $semester)
                <tr>
                    <td>
                        <strong>{{ $semester->level }}</strong>
                    </td>

                    <td>
                        <strong>{{ $semester->name }}</strong>
                        <br>
                        <small>{{ $semester->description ?? '-' }}</small>
                    </td>

                    <td>{{ $semester->courses_count }}</td>

                    <td>{{ $semester->students_count }}</td>

                    <td>{{ $semester->enrollments_count }}</td>

                    <td>
                        <span class="badge {{ $semester->is_active ? 'badge-green' : 'badge-red' }}">
                            {{ $semester->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>

                    <td class="actions-inline">
                        <a class="btn btn-sm" href="{{ route('admin.semester.show', $semester) }}">
                            Detail
                        </a>

                        <a class="btn btn-sm" href="{{ route('admin.semester.edit', $semester) }}">
                            Edit
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Belum ada semester mahasiswa.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">
    {{ $studySemesters->links() }}
</div>
@endsection