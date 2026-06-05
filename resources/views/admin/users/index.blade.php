@extends('layouts.app')

@section('title', 'Kelola Asisten & Mahasiswa')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $users = $users ?? collect();
    $studySemesters = $studySemesters ?? collect();

    $dashboardUrl = Route::has('admin.dashboard')
        ? route('admin.dashboard')
        : '#';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Kelola Asisten & Mahasiswa</h1>

    <p>
        Tambah, edit, hapus, verifikasi, dan atur akun asisten praktikum serta mahasiswa.
        Akun admin utama tetap dikelola manual.
    </p>

    <div class="hero-actions">
        <a href="{{ $dashboardUrl }}" class="btn">
            ← Dashboard
        </a>

        <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
            + Tambah Asisten / Mahasiswa
        </a>
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Filter User</h2>
            <div class="section-subtitle">
                Cari user berdasarkan nama, email, NIM/NIP, jenis akun, semester, atau status akun.
            </div>
        </div>
    </div>

    <form method="GET" class="actions-inline">
        <input
            class="form-control"
            style="width: 260px;"
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari nama/email/NIM"
        >

        <select class="form-control" style="width: 190px;" name="role">
            <option value="">Semua jenis akun</option>

            <option value="asisten" @selected(request('role') === 'asisten')>
                Asisten Praktikum
            </option>

            <option value="mahasiswa" @selected(request('role') === 'mahasiswa')>
                Mahasiswa
            </option>
        </select>

        <select class="form-control" style="width: 190px;" name="study_semester_id">
            <option value="">Semua semester</option>

            @foreach($studySemesters as $semester)
                <option
                    value="{{ $semester->id }}"
                    @selected((string) request('study_semester_id') === (string) $semester->id)
                >
                    {{ $semester->name }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width: 190px;" name="status">
            <option value="">Semua status</option>

            <option value="active" @selected(request('status') === 'active')>
                Aktif
            </option>

            <option value="pending" @selected(request('status') === 'pending')>
                Pending / Nonaktif
            </option>
        </select>

        <button class="btn" type="submit">
            Filter
        </button>

        @if(request()->hasAny(['search', 'role', 'study_semester_id', 'status']))
            <a href="{{ route('admin.users.index') }}" class="btn">
                Reset
            </a>
        @endif
    </form>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Asisten & Mahasiswa</h2>
            <div class="section-subtitle">
                User yang tampil di sini adalah akun asisten praktikum dan mahasiswa.
            </div>
        </div>

        <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">
            + Tambah User
        </a>
    </div>

    @if($users->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">👥</div>

            <h3 class="empty-state-title">
                Belum ada asisten atau mahasiswa
            </h3>

            <p class="empty-state-text">
                Data user akan tampil setelah admin menambahkan akun asisten atau mahasiswa.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Jenis Akun</th>
                            <th>Semester / Rombel</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($users as $user)
                            @php
                                $currentRole = $user->roles->pluck('name')->first() ?: $user->role;

                                $roleLabel = match ($currentRole) {
                                    'asisten' => 'Asisten Praktikum',
                                    'mahasiswa' => 'Mahasiswa',
                                    default => ucfirst($currentRole ?? '-'),
                                };

                                $roleClass = match ($currentRole) {
                                    'asisten' => 'status-info',
                                    'mahasiswa' => 'status-success',
                                    default => 'status-muted',
                                };

                                $isStudent = $currentRole === 'mahasiswa';
                                $isPendingStudent = $isStudent && ! $user->is_active;
                            @endphp

                            <tr>
                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $user->name }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ $user->nim_nip ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $user->email }}
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill {{ $roleClass }}">
                                        {{ $roleLabel }}
                                    </span>
                                </td>

                                <td>
                                    @if($isStudent)
                                        <div style="display: grid; gap: 5px;">
                                            <strong>
                                                {{ $user->studySemester?->name ?? '-' }}
                                            </strong>

                                            @if($user->student_group)
                                                <span class="item-meta" style="margin-top: 0;">
                                                    Kelas/Rombel {{ strtoupper($user->student_group) }}
                                                </span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="item-meta" style="margin-top: 0;">
                                            -
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if($isPendingStudent)
                                        <span class="status-pill status-danger">
                                            Pending Verifikasi
                                        </span>
                                    @else
                                        <span class="status-pill {{ $user->is_active ? 'status-success' : 'status-danger' }}">
                                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <div class="actions-inline">
                                        @if($isPendingStudent && Route::has('admin.users.verify'))
                                            <form
                                                method="POST"
                                                action="{{ route('admin.users.verify', $user) }}"
                                                onsubmit="return confirm('Verifikasi akun mahasiswa ini? Email aktivasi akan dikirim ke mahasiswa.')"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    Verifikasi
                                                </button>
                                            </form>
                                        @endif

                                        <a class="btn btn-sm" href="{{ route('admin.users.show', $user) }}">
                                            Detail
                                        </a>

                                        <a class="btn btn-sm" href="{{ route('admin.users.edit', $user) }}">
                                            Edit
                                        </a>

                                        @include('partials.delete-button', [
                                            'action' => route('admin.users.destroy', $user)
                                        ])
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 18px;">
            {{ $users->links() }}
        </div>
    @endif
</section>
@endsection