@extends('layouts.app')

@section('title', 'Detail Mata Kuliah')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Detail Mata Kuliah',
    'description' => 'Detail mata kuliah, semester, tahun akademik, dan kelas praktikum.'
])

<div class="form-card">
    <div class="grid grid-4">
        <div>
            <p><strong>Kode:</strong></p>
            <p>{{ $course->code }}</p>
        </div>

        <div>
            <p><strong>Nama:</strong></p>
            <p>{{ $course->name }}</p>
        </div>

        <div>
            <p><strong>SKS:</strong></p>
            <p>{{ $course->sks }}</p>
        </div>

        <div>
            <p><strong>Status:</strong></p>
            <p>
                <span class="badge {{ $course->is_active ? 'badge-green' : 'badge-red' }}">
                    {{ $course->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </p>
        </div>
    </div>

    <div class="grid grid-4" style="margin-top:16px;">
        <div>
            <p><strong>Semester Mahasiswa:</strong></p>
            <p>{{ $course->studySemester?->name ?? '-' }}</p>
        </div>

        <div>
            <p><strong>Tahun Akademik:</strong></p>
            <p>
                {{ $course->academicYear?->year ?? '-' }}
                @if($course->academicYear)
                    - {{ ucfirst($course->academicYear->semester) }}
                @endif
            </p>
        </div>

        <div>
            <p><strong>Jumlah Mahasiswa Semester:</strong></p>
            <p>{{ $course->studySemester?->students?->count() ?? 0 }}</p>
        </div>

        <div>
            <p><strong>Jumlah Kelas Praktikum:</strong></p>
            <p>{{ $course->classes_count }}</p>
        </div>
    </div>

    <div class="form-actions">
        <a class="btn" href="{{ route('admin.matakuliah.index') }}">Kembali</a>

        <a class="btn btn-primary" href="{{ route('admin.matakuliah.edit', $course) }}">
            Edit
        </a>

        @if($course->classes_count === 0)
            @include('partials.delete-button', [
                'action' => route('admin.matakuliah.destroy', $course),
                'confirm' => 'Yakin ingin menghapus mata kuliah ini?'
            ])
        @endif
    </div>
</div>

<div class="table-card" style="margin-top:16px;">
    <table>
        <thead>
            <tr>
                <th>Kelas Praktikum</th>
                <th>Asisten</th>
                <th>Ruang</th>
                <th>Jadwal</th>
                <th>Mahasiswa</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($course->classes as $class)
                <tr>
                    <td>
                        <strong>{{ $class->name }}</strong>
                    </td>

                    <td>
                        {{ $class->assistant?->name ?? '-' }}
                    </td>

                    <td>
                        {{ $class->room ?? '-' }}
                    </td>

                    <td>
                        {{ $class->schedule ?? '-' }}
                    </td>

                    <td>
                        <strong>{{ $class->resolved_students_count ?? 0 }}</strong>
                        <br>
                        <small>Manual: {{ $class->students->count() }}</small>
                    </td>

                    <td>
                        <span class="badge {{ $class->is_active ? 'badge-green' : 'badge-red' }}">
                            {{ $class->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>

                    <td>
                        <a class="btn btn-sm" href="{{ route('admin.kelas.show', $class) }}">
                            Detail
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        Belum ada kelas praktikum untuk mata kuliah ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<p style="margin-top:12px; color:#64748b;">
    Catatan: jumlah mahasiswa semester berasal dari semester yang dipilih. Mahasiswa manual adalah mahasiswa yang dimasukkan khusus melalui relasi kelas.
</p>
@endsection