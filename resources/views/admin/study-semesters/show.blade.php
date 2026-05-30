@extends('layouts.app')

@section('title', $studySemester->name)

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => $studySemester->name,
    'description' => 'Detail semester, mata kuliah, kelas praktikum, dan mahasiswa.'
])

<div class="form-card">
    <div class="grid grid-4">
        <div>
            <p><strong>Level:</strong></p>
            <p>{{ $studySemester->level }}</p>
        </div>

        <div>
            <p><strong>Status:</strong></p>
            <p>
                <span class="badge {{ $studySemester->is_active ? 'badge-green' : 'badge-red' }}">
                    {{ $studySemester->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </p>
        </div>

        <div>
            <p><strong>Jumlah Mata Kuliah:</strong></p>
            <p>{{ $studySemester->courses_count }}</p>
        </div>

        <div>
            <p><strong>Jumlah Mahasiswa:</strong></p>
            <p>{{ $studySemester->students_count }}</p>
        </div>
    </div>

    <div style="margin-top:16px;">
        <p><strong>Deskripsi:</strong></p>
        <p>{{ $studySemester->description ?? '-' }}</p>
    </div>

    <div class="form-actions">
        <a class="btn" href="{{ route('admin.semester.index') }}">Kembali</a>
        <a class="btn btn-primary" href="{{ route('admin.semester.edit', $studySemester) }}">Edit</a>

        @if($studySemester->courses_count === 0 && $studySemester->students_count === 0 && $studySemester->enrollments_count === 0)
            @include('partials.delete-button', [
                'action' => route('admin.semester.destroy', $studySemester),
                'confirm' => 'Yakin ingin menghapus semester ini?'
            ])
        @endif
    </div>
</div>

<div class="table-card" style="margin-top:16px;">
    <table>
        <thead>
            <tr>
                <th>Mata Kuliah</th>
                <th>Kode</th>
                <th>SKS</th>
                <th>Tahun Akademik</th>
                <th>Kelas Praktikum</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($studySemester->courses as $course)
                <tr>
                    <td>
                        <a href="{{ route('admin.matakuliah.show', $course) }}">
                            <strong>{{ $course->name }}</strong>
                        </a>
                    </td>

                    <td>{{ $course->code ?? '-' }}</td>

                    <td>{{ $course->sks ?? '-' }}</td>

                    <td>{{ $course->academicYear?->name ?? '-' }}</td>

                    <td>
                        @forelse($course->classes as $class)
                            <div style="margin-bottom:8px;">
                                <a href="{{ route('admin.kelas.show', $class) }}">
                                    <strong>{{ $class->name }}</strong>
                                </a>
                                <br>
                                <small>
                                    Asisten: {{ $class->assistant?->name ?? '-' }},
                                    Ruang: {{ $class->room ?? '-' }},
                                    Jadwal: {{ $class->schedule ?? '-' }}
                                </small>
                            </div>
                        @empty
                            -
                        @endforelse
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        Belum ada mata kuliah untuk semester ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="table-card" style="margin-top:16px;">
    <table>
        <thead>
            <tr>
                <th>Mahasiswa</th>
                <th>NIM</th>
                <th>Email</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($studySemester->students as $student)
                <tr>
                    <td>
                        <strong>{{ $student->name }}</strong>
                    </td>

                    <td>{{ $student->nim_nip ?? '-' }}</td>

                    <td>{{ $student->email }}</td>

                    <td>
                        <span class="badge {{ $student->is_active ? 'badge-green' : 'badge-red' }}">
                            {{ $student->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>

                    <td>
                        <a class="btn btn-sm" href="{{ route('admin.users.show', $student) }}">
                            Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        Belum ada mahasiswa di semester ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection