@extends('layouts.app')

@section('title', 'Detail Tahun Akademik')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Detail Tahun Akademik',
    'description' => 'Detail periode tahun akademik dan mata kuliah yang memakai periode ini.'
])

<div class="form-card">
    <div class="grid grid-4">
        <div>
            <p><strong>Tahun:</strong></p>
            <p>{{ $academicYear->year }}</p>
        </div>

        <div>
            <p><strong>Periode:</strong></p>
            <p>{{ ucfirst($academicYear->semester) }}</p>
        </div>

        <div>
            <p><strong>Status:</strong></p>
            <p>
                <span class="badge {{ $academicYear->is_active ? 'badge-green' : 'badge-red' }}">
                    {{ $academicYear->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </p>
        </div>

        <div>
            <p><strong>Jumlah Mata Kuliah:</strong></p>
            <p>{{ $academicYear->courses_count }}</p>
        </div>
    </div>

    <div class="form-actions">
        <a class="btn" href="{{ route('admin.tahun-akademik.index') }}">
            Kembali
        </a>

        <a class="btn btn-primary" href="{{ route('admin.tahun-akademik.edit', $academicYear) }}">
            Edit
        </a>

        @if($academicYear->courses_count === 0)
            @include('partials.delete-button', [
                'action' => route('admin.tahun-akademik.destroy', $academicYear),
                'confirm' => 'Yakin ingin menghapus tahun akademik ini?'
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
                <th>Semester Mahasiswa</th>
                <th>SKS</th>
                <th>Kelas Praktikum</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse($academicYear->courses as $course)
                <tr>
                    <td>
                        <a href="{{ route('admin.matakuliah.show', $course) }}">
                            <strong>{{ $course->name }}</strong>
                        </a>
                    </td>

                    <td>
                        {{ $course->code }}
                    </td>

                    <td>
                        {{ $course->studySemester?->name ?? '-' }}
                    </td>

                    <td>
                        {{ $course->sks }}
                    </td>

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

                    <td>
                        <span class="badge {{ $course->is_active ? 'badge-green' : 'badge-red' }}">
                            {{ $course->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        Belum ada mata kuliah yang memakai tahun akademik ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection