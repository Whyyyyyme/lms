@extends('layouts.app')

@section('title', 'Detail Kelas Praktikum')

@section('content')
@php
    $automaticStudents = $automaticStudents ?? collect();

    $classTypeLabel = match ($praktikumClass->class_type ?? 'regular') {
        'combined' => 'Gabungan',
        default => 'Reguler',
    };

    $groupMembers = collect($praktikumClass->group_members ?? [])
        ->filter()
        ->values();

    $groupDisplay = '-';

    if (($praktikumClass->class_type ?? 'regular') === 'regular') {
        $groupDisplay = $praktikumClass->student_group
            ? 'Kelas ' . $praktikumClass->student_group
            : '-';
    }

    if (($praktikumClass->class_type ?? 'regular') === 'combined') {
        $groupDisplay = $groupMembers->isNotEmpty()
            ? $groupMembers->map(fn ($group) => 'Kelas ' . $group)->implode(', ')
            : '-';
    }
@endphp

@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Detail Kelas Praktikum',
    'description' => 'Detail kelas, mata kuliah, asisten, mahasiswa otomatis, mahasiswa manual, materi, tugas, dan absensi.'
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
            <p><strong>Tipe Kelas:</strong></p>
            <p>
                <span class="badge {{ ($praktikumClass->class_type ?? 'regular') === 'combined' ? 'badge-blue' : 'badge-green' }}">
                    {{ $classTypeLabel }}
                </span>
            </p>
        </div>

        <div>
            <p><strong>Rombel:</strong></p>
            <p>{{ $groupDisplay }}</p>
        </div>

        <div>
            <p><strong>Label Gabungan:</strong></p>
            <p>{{ $praktikumClass->group_label ?? '-' }}</p>
        </div>

        <div>
            <p><strong>Mahasiswa Otomatis:</strong></p>
            <p>{{ $automaticStudents->count() }}</p>
        </div>
    </div>

    <div class="grid grid-4" style="margin-top:16px;">
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

        <div>
            <p><strong>Absensi:</strong></p>
            <p>{{ $praktikumClass->attendances_count }}</p>
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
    <div style="padding:16px 16px 0;">
        <p class="mb-1 text-sm font-semibold text-slate-700">
            Mahasiswa Otomatis Berdasarkan Semester + Rombel
        </p>

        <p class="mb-3 text-xs text-slate-500">
            Daftar ini dihitung otomatis dari semester mata kuliah dan rombel kelas praktikum.
            Untuk kelas reguler, sistem mengambil mahasiswa sesuai satu rombel.
            Untuk kelas gabungan, sistem mengambil mahasiswa dari rombel yang digabung.
        </p>
    </div>

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
            @forelse($automaticStudents as $student)
                <tr>
                    <td>
                        <strong>{{ $student->name }}</strong>
                    </td>

                    <td>{{ $student->nim_nip ?? '-' }}</td>

                    <td>{{ $student->studySemester?->name ?? '-' }}</td>

                    <td>
                        {{ $student->student_group ? 'Kelas ' . $student->student_group : '-' }}
                    </td>

                    <td>{{ $student->email }}</td>

                    <td>
                        <span class="badge {{ $student->is_active ? 'badge-green' : 'badge-red' }}">
                            {{ $student->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        Belum ada mahasiswa otomatis untuk semester dan rombel kelas ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="table-card" style="margin-top:16px;">
    <div style="padding:16px 16px 0;">
        <p class="mb-1 text-sm font-semibold text-slate-700">
            Mahasiswa Manual / Khusus
        </p>

        <p class="mb-3 text-xs text-slate-500">
            Daftar ini hanya untuk tambahan khusus. Akses utama mahasiswa seharusnya dihitung otomatis dari semester dan rombel.
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Mahasiswa Manual / Khusus</th>
                <th>NIM</th>
                <th>Semester</th>
                <th>Rombel</th>
                <th>Email</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
            @forelse($praktikumClass->students as $student)
                <tr>
                    <td>
                        <strong>{{ $student->name }}</strong>
                    </td>

                    <td>{{ $student->nim_nip ?? '-' }}</td>

                    <td>{{ $student->studySemester?->name ?? '-' }}</td>

                    <td>
                        {{ $student->student_group ? 'Kelas ' . $student->student_group : '-' }}
                    </td>

                    <td>{{ $student->email }}</td>

                    <td>
                        <span class="badge {{ $student->is_active ? 'badge-green' : 'badge-red' }}">
                            {{ $student->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        Tidak ada mahasiswa manual. Mahasiswa otomatis tetap bisa mengakses kelas ini jika semester dan rombel mereka sesuai.
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