@extends('layouts.app')

@php
    $currentRole = $user->roles->pluck('name')->first() ?: $user->role;

    $roleLabel = match ($currentRole) {
        'asisten' => 'Asisten Praktikum',
        'mahasiswa' => 'Mahasiswa',
        default => ucfirst($currentRole ?? '-'),
    };
@endphp

@section('title', 'Detail ' . $roleLabel)

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Detail ' . $roleLabel,
    'description' => 'Informasi akun, status, dan relasi data pengguna.'
])

<div class="form-card">
    <div class="grid grid-2">
        <div>
            <p><strong>Nama:</strong></p>
            <p>{{ $user->name }}</p>
        </div>

        <div>
            <p><strong>Email:</strong></p>
            <p>{{ $user->email }}</p>
        </div>

        <div>
            <p><strong>{{ $currentRole === 'mahasiswa' ? 'NIM' : 'NIP / Kode Asisten' }}:</strong></p>
            <p>{{ $user->nim_nip ?? '-' }}</p>
        </div>

        <div>
            <p><strong>Jenis Akun:</strong></p>
            <p>
                <span class="badge badge-blue">
                    {{ $roleLabel }}
                </span>
            </p>
        </div>

        <div>
            <p><strong>Status:</strong></p>
            <p>
                <span class="badge {{ $user->is_active ? 'badge-green' : 'badge-red' }}">
                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                </span>
            </p>
        </div>

        <div>
            <p><strong>Dibuat:</strong></p>
            <p>{{ $user->created_at?->format('d M Y H:i') ?? '-' }}</p>
        </div>
    </div>

    <div class="form-actions">
        <a class="btn" href="{{ route('admin.users.index') }}">Kembali</a>
        <a class="btn btn-primary" href="{{ route('admin.users.edit', $user) }}">Edit</a>
    </div>
</div>

@if($currentRole === 'mahasiswa')
    <div class="form-card" style="margin-top:16px;">
        <h3 style="margin-bottom:12px;">Informasi Mahasiswa</h3>

        <p>
            <strong>Semester Mahasiswa:</strong>
            {{ $user->studySemester?->name ?? 'Belum diatur' }}
        </p>

        <p style="margin-top:8px; color:#64748b;">
            Mata kuliah mahasiswa mengikuti semester yang dipilih.
        </p>
    </div>

    <div class="table-card" style="margin-top:16px;">
        <table>
            <thead>
                <tr>
                    <th>Mata Kuliah</th>
                    <th>Kode</th>
                    <th>SKS</th>
                    <th>Kelas Praktikum</th>
                </tr>
            </thead>

            <tbody>
                @forelse($user->studySemester?->courses ?? [] as $course)
                    <tr>
                        <td>
                            <strong>{{ $course->name }}</strong>
                        </td>

                        <td>{{ $course->code ?? '-' }}</td>

                        <td>{{ $course->sks ?? '-' }}</td>

                        <td>
                            @forelse($course->classes as $class)
                                <div style="margin-bottom:6px;">
                                    <a href="{{ route('admin.kelas.show', $class) }}">
                                        {{ $class->name }}
                                    </a>
                                    <br>
                                    <small>
                                        Ruang: {{ $class->room ?? '-' }},
                                        Jadwal: {{ $class->schedule ?? '-' }}
                                    </small>
                                </div>
                            @empty
                                <span>-</span>
                            @endforelse
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            Belum ada mata kuliah untuk semester mahasiswa ini.
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
                    <th>Kelas Manual / Khusus</th>
                    <th>Mata Kuliah</th>
                    <th>Semester</th>
                    <th>Jadwal</th>
                </tr>
            </thead>

            <tbody>
                @forelse($user->kelasDiikuti as $class)
                    <tr>
                        <td>
                            <a href="{{ route('admin.kelas.show', $class) }}">
                                <strong>{{ $class->name }}</strong>
                            </a>
                        </td>
                        <td>{{ $class->course?->name ?? '-' }}</td>
                        <td>{{ $class->course?->studySemester?->name ?? '-' }}</td>
                        <td>{{ $class->schedule ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            Tidak ada kelas manual. Mahasiswa tetap bisa mengakses kelas berdasarkan semester.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif

@if($currentRole === 'asisten')
    <div class="table-card" style="margin-top:16px;">
        <table>
            <thead>
                <tr>
                    <th>Kelas Diasisteni</th>
                    <th>Mata Kuliah</th>
                    <th>Semester</th>
                    <th>Ruang</th>
                    <th>Jadwal</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($user->kelasDiasisteni as $class)
                    <tr>
                        <td>
                            <a href="{{ route('admin.kelas.show', $class) }}">
                                <strong>{{ $class->name }}</strong>
                            </a>
                        </td>

                        <td>{{ $class->course?->name ?? '-' }}</td>
                        <td>{{ $class->course?->studySemester?->name ?? '-' }}</td>
                        <td>{{ $class->room ?? '-' }}</td>
                        <td>{{ $class->schedule ?? '-' }}</td>

                        <td>
                            <span class="badge {{ $class->is_active ? 'badge-green' : 'badge-red' }}">
                                {{ $class->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">
                            Asisten ini belum dihubungkan ke kelas praktikum.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p style="margin-top:12px; color:#64748b;">
        Catatan: Asisten dihubungkan ke kelas melalui menu <strong>Kelola Kelas</strong>, bukan lewat form user.
    </p>
@endif
@endsection