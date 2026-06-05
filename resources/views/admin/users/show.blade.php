@extends('layouts.app')

@php
    use Illuminate\Support\Facades\Route;

    $currentRole = $user->roles->pluck('name')->first() ?: $user->role;

    $roleLabel = match ($currentRole) {
        'asisten' => 'Asisten Praktikum',
        'mahasiswa' => 'Mahasiswa',
        default => ucfirst($currentRole ?? '-'),
    };

    $roleClass = match ($currentRole) {
        'asisten' => 'status-info',
        'mahasiswa' => 'status-success',
        'admin' => 'status-warning',
        default => 'status-muted',
    };

    $safe = fn (string $name, array $params = []) => Route::has($name)
        ? route($name, $params)
        : '#';

    $backUrl = Route::has('admin.users.index')
        ? route('admin.users.index')
        : (Route::has('admin.dashboard') ? route('admin.dashboard') : '#');

    $createdAt = $user->created_at
        ? $user->created_at->format('d M Y H:i')
        : '-';

    $updatedAt = $user->updated_at
        ? $user->updated_at->format('d M Y H:i')
        : '-';

    $isStudent = $currentRole === 'mahasiswa';
    $isAssistant = $currentRole === 'asisten';
@endphp

@section('title', 'Detail ' . $roleLabel)

@section('content')
<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Detail {{ $roleLabel }}</h1>

    <p>
        Informasi akun, status aktif, jenis akun, semester, rombel, dan relasi kelas pengguna.
    </p>

    <div class="hero-actions">
        <a href="{{ $backUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('admin.users.edit'))
            <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">
                Edit User
            </a>
        @endif
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Informasi Akun</h2>
            <div class="section-subtitle">
                Detail identitas akun dan status pengguna di sistem.
            </div>
        </div>

        <span class="status-pill {{ $user->is_active ? 'status-success' : 'status-danger' }}">
            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
        </span>
    </div>

    <div class="grid grid-4">
        <div class="stat-card">
            <div class="stat-label">Nama</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $user->name }}
            </div>
            <div class="stat-note">
                Nama lengkap pengguna.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Email</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $user->email }}
            </div>
            <div class="stat-note">
                Email aktif untuk akun LMS.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">
                {{ $isStudent ? 'NIM' : 'NIP / Kode Asisten' }}
            </div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $user->nim_nip ?? '-' }}
            </div>
            <div class="stat-note">
                Identitas akademik pengguna.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Jenis Akun</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $roleLabel }}
            </div>
            <div class="stat-note">
                Role pengguna pada sistem.
            </div>
        </div>
    </div>

    <div class="grid grid-3" style="margin-top: 18px;">
        <div class="stat-card">
            <div class="stat-label">Status Akun</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
            </div>
            <div class="stat-note">
                Status akses pengguna.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Dibuat</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $createdAt }}
            </div>
            <div class="stat-note">
                Waktu akun dibuat.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Diperbarui</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $updatedAt }}
            </div>
            <div class="stat-note">
                Waktu terakhir akun diperbarui.
            </div>
        </div>
    </div>
</section>

@if($isStudent)
    <section class="card" style="margin-bottom: 18px;">
        <div class="section-header">
            <div>
                <h2 class="section-title">Informasi Mahasiswa</h2>
                <div class="section-subtitle">
                    Semester dan rombel mahasiswa menjadi dasar akses mata kuliah dan kelas praktikum.
                </div>
            </div>

            <span class="status-pill {{ $roleClass }}">
                {{ $roleLabel }}
            </span>
        </div>

        <div class="grid grid-3">
            <div class="stat-card">
                <div class="stat-label">Semester Mahasiswa</div>
                <div class="stat-value" style="font-size: 20px;">
                    {{ $user->studySemester?->name ?? 'Belum diatur' }}
                </div>
                <div class="stat-note">
                    Mata kuliah mahasiswa mengikuti semester ini.
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Kelas / Rombel</div>
                <div class="stat-value" style="font-size: 20px;">
                    {{ $user->student_group ? 'Kelas '.strtoupper($user->student_group) : '-' }}
                </div>
                <div class="stat-note">
                    Rombel digunakan untuk pencocokan kelas praktikum.
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-label">Status Akses</div>
                <div class="stat-value" style="font-size: 20px;">
                    {{ $user->is_active ? 'Aktif' : 'Pending / Nonaktif' }}
                </div>
                <div class="stat-note">
                    Mahasiswa aktif dapat mengakses kelas sesuai semester dan rombel.
                </div>
            </div>
        </div>
    </section>

    <section class="card" style="margin-bottom: 18px;">
        <div class="section-header">
            <div>
                <h2 class="section-title">Mata Kuliah Berdasarkan Semester</h2>
                <div class="section-subtitle">
                    Daftar mata kuliah yang terhubung dengan semester mahasiswa.
                </div>
            </div>
        </div>

        @if(($user->studySemester?->courses ?? collect())->isEmpty())
            <div class="empty-state">
                <div style="font-size: 34px; margin-bottom: 8px;">📚</div>
                <h3 class="empty-state-title">Belum ada mata kuliah</h3>
                <p class="empty-state-text">
                    Belum ada mata kuliah untuk semester mahasiswa ini.
                </p>
            </div>
        @else
            <div class="table-card">
                <div class="table-scroll">
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
                            @foreach($user->studySemester?->courses ?? [] as $course)
                                <tr>
                                    <td>
                                        <strong>{{ $course->name }}</strong>
                                    </td>

                                    <td>
                                        <span class="status-pill status-muted">
                                            {{ $course->code ?? '-' }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $course->sks ?? '-' }}
                                    </td>

                                    <td>
                                        @forelse($course->classes as $class)
                                            <div style="margin-bottom: 10px;">
                                                <a href="{{ $safe('admin.kelas.show', [$class]) }}">
                                                    <strong>{{ $class->name }}</strong>
                                                </a>

                                                <div class="item-meta" style="margin-top: 2px;">
                                                    Ruang: {{ $class->room ?? '-' }},
                                                    Jadwal: {{ $class->schedule ?? '-' }}
                                                </div>
                                            </div>
                                        @empty
                                            <span class="item-meta" style="margin-top: 0;">-</span>
                                        @endforelse
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>

    <section class="card">
        <div class="section-header">
            <div>
                <h2 class="section-title">Kelas Manual / Khusus</h2>
                <div class="section-subtitle">
                    Kelas tambahan yang dihubungkan secara manual kepada mahasiswa.
                </div>
            </div>
        </div>

        @if(($user->kelasDiikuti ?? collect())->isEmpty())
            <div class="empty-state">
                <div style="font-size: 34px; margin-bottom: 8px;">🏫</div>
                <h3 class="empty-state-title">Tidak ada kelas manual</h3>
                <p class="empty-state-text">
                    Mahasiswa tetap bisa mengakses kelas berdasarkan semester dan rombel.
                </p>
            </div>
        @else
            <div class="table-card">
                <div class="table-scroll">
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
                            @foreach($user->kelasDiikuti as $class)
                                <tr>
                                    <td>
                                        <a href="{{ $safe('admin.kelas.show', [$class]) }}">
                                            <strong>{{ $class->name }}</strong>
                                        </a>
                                    </td>

                                    <td>{{ $class->course?->name ?? '-' }}</td>
                                    <td>{{ $class->course?->studySemester?->name ?? '-' }}</td>
                                    <td>{{ $class->schedule ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif
    </section>
@endif

@if($isAssistant)
    <section class="card">
        <div class="section-header">
            <div>
                <h2 class="section-title">Kelas Diasisteni</h2>
                <div class="section-subtitle">
                    Daftar kelas praktikum yang dihubungkan ke asisten ini melalui menu Kelola Kelas.
                </div>
            </div>

            <span class="status-pill {{ $roleClass }}">
                {{ $roleLabel }}
            </span>
        </div>

        @if(($user->kelasDiasisteni ?? collect())->isEmpty())
            <div class="empty-state">
                <div style="font-size: 34px; margin-bottom: 8px;">🏫</div>
                <h3 class="empty-state-title">Belum ada kelas</h3>
                <p class="empty-state-text">
                    Asisten ini belum dihubungkan ke kelas praktikum.
                </p>
            </div>
        @else
            <div class="table-card">
                <div class="table-scroll">
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
                            @foreach($user->kelasDiasisteni as $class)
                                <tr>
                                    <td>
                                        <a href="{{ $safe('admin.kelas.show', [$class]) }}">
                                            <strong>{{ $class->name }}</strong>
                                        </a>
                                    </td>

                                    <td>{{ $class->course?->name ?? '-' }}</td>
                                    <td>{{ $class->course?->studySemester?->name ?? '-' }}</td>
                                    <td>{{ $class->room ?? '-' }}</td>
                                    <td>{{ $class->schedule ?? '-' }}</td>

                                    <td>
                                        <span class="status-pill {{ $class->is_active ? 'status-success' : 'status-danger' }}">
                                            {{ $class->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        <div class="alert" style="margin-top: 18px;">
            <strong>Catatan:</strong>
            Asisten dihubungkan ke kelas melalui menu <strong>Kelola Kelas</strong>, bukan lewat form user.
        </div>
    </section>
@endif

<section class="card" style="margin-top: 22px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Aksi User</h2>
            <div class="section-subtitle">
                Kembali ke daftar user atau ubah data akun pengguna.
            </div>
        </div>
    </div>

    <div class="form-actions">
        <a class="btn" href="{{ $backUrl }}">
            Kembali
        </a>

        @if(Route::has('admin.users.edit'))
            <a class="btn btn-primary" href="{{ route('admin.users.edit', $user) }}">
                Edit User
            </a>
        @endif
    </div>
</section>
@endsection