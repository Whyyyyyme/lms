@extends('layouts.app')

@section('title', 'Detail Kelas Praktikum')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Detail Kelas Praktikum',
    'description' => 'Detail kelas, mata kuliah, asisten, mahasiswa, materi, tugas, dan absensi.'
])

<div class="form-card">
    <div class="grid grid-4">
        <div>
            <p><strong>Kelas:</strong></p>
            <p>{{ $praktikumClass->name }}</p>
        </div>

        <div>
            <p><strong>Status:</strong></p>
            <p>
                <span class="badge {{ $praktikumClass->is_active ? 'badge-green' : 'badge-red' }}">
                    {{ $praktikumClass->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </p>
        </div>

        <div>
            <p><strong>Ruangan:</strong></p>
            <p>{{ $praktikumClass->room ?? '-' }}</p>
        </div>

        <div>
            <p><strong>Jadwal:</strong></p>
            <p>{{ $praktikumClass->schedule ?? '-' }}</p>
        </div>
    </div>

    <div class="grid grid-4" style="margin-top:16px;">
        <div>
            <p><strong>Mata Kuliah:</strong></p>
            <p>{{ $praktikumClass->course?->code ?? '-' }} - {{ $praktikumClass->course?->name ?? '-' }}</p>
        </div>

        <div>
            <p><strong>Semester:</strong></p>
            <p>{{ $praktikumClass->course?->studySemester?->name ?? '-' }}</p>
        </div>

        <div>
            <p><strong>Tahun Akademik:</strong></p>
            <p>
                {{ $praktikumClass->course?->academicYear?->year ?? '-' }}
                @if($praktikumClass->course?->academicYear)
                    - {{ ucfirst($praktikumClass->course->academicYear->semester) }}
                @endif
            </p>
        </div>

        <div>
            <p><strong>Asisten:</strong></p>
            <p>{{ $praktikumClass->assistant?->name ?? 'Belum ditentukan' }}</p>
        </div>
    </div>

    <div class="grid grid-4" style="margin-top:16px;">
        <div>
            <p><strong>Total Mahasiswa Akses:</strong></p>
            <p>{{ $resolvedStudents->count() }}</p>
        </div>

        <div>
            <p><strong>Mahasiswa Manual:</strong></p>
            <p>{{ $praktikumClass->students_count }}</p>
        </div>

        <div>
            <p><strong>Materi:</strong></p>
            <p>{{ $praktikumClass->materials_count }}</p>
        </div>

        <div>
            <p><strong>Tugas:</strong></p>
            <p>{{ $praktikumClass->assignments_count }}</p>
        </div>
    </div>

    <div class="form-actions">
        <a class="btn" href="{{ route('admin.kelas.index') }}">Kembali</a>

        <a class="btn btn-primary" href="{{ route('admin.kelas.edit', $praktikumClass) }}">
            Edit
        </a>

        @if($praktikumClass->materials_count === 0 && $praktikumClass->assignments_count === 0 && $praktikumClass->attendances_count === 0 && $praktikumClass->announcements_count === 0)
            @include('partials.delete-button', [
                'action' => route('admin.kelas.destroy', $praktikumClass),
                'confirm' => 'Yakin ingin menghapus kelas ini?'
            ])
        @endif
    </div>
</div>

<div class="table-card" style="margin-top:16px;">
    <table>
        <thead>
            <tr>
                <th>Mahasiswa yang Bisa Mengakses</th>
                <th>NIM</th>
                <th>Semester</th>
                <th>Email</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse($resolvedStudents as $student)
                <tr>
                    <td>
                        <strong>{{ $student->name }}</strong>
                    </td>

                    <td>{{ $student->nim_nip ?? '-' }}</td>

                    <td>{{ $student->studySemester?->name ?? '-' }}</td>

                    <td>{{ $student->email }}</td>

                    <td>
                        <span class="badge {{ $student->is_active ? 'badge-green' : 'badge-red' }}">
                            {{ $student->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        Belum ada mahasiswa aktif yang cocok dengan semester mata kuliah atau pembagian manual kelas ini.
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
                <th>Materi</th>
                <th>Tipe</th>
                <th>Dibuat Oleh</th>
                <th>Publikasi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($praktikumClass->materials as $material)
                <tr>
                    <td>
                        <strong>{{ $material->title }}</strong>
                    </td>

                    <td>{{ $material->type ?? '-' }}</td>

                    <td>{{ $material->creator?->name ?? '-' }}</td>

                    <td>{{ $material->published_at?->format('d M Y H:i') ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">
                        Belum ada materi pada kelas ini.
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
                <th>Tugas</th>
                <th>Deadline</th>
                <th>Nilai Maksimal</th>
                <th>Dibuat Oleh</th>
            </tr>
        </thead>

        <tbody>
            @forelse($praktikumClass->assignments as $assignment)
                <tr>
                    <td>
                        <strong>{{ $assignment->title }}</strong>
                    </td>

                    <td>{{ $assignment->deadline?->format('d M Y H:i') ?? '-' }}</td>

                    <td>{{ $assignment->max_score ?? '-' }}</td>

                    <td>{{ $assignment->creator?->name ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">
                        Belum ada tugas pada kelas ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection