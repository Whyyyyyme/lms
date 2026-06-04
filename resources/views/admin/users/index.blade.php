@extends('layouts.app')

@section('title', 'Kelola Asisten & Mahasiswa')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Kelola Asisten & Mahasiswa',
    'description' => 'Tambah, edit, hapus, verifikasi, dan atur akun asisten praktikum serta mahasiswa. Akun admin utama dikelola manual.'
])

<div class="toolbar">
    <form method="GET" class="actions-inline">
        <input
            class="form-control"
            style="width:260px;"
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari nama/email/NIM"
        >

        <select class="form-control" style="width:190px;" name="role">
            <option value="">Semua jenis akun</option>
            <option value="asisten" @selected(request('role') === 'asisten')>
                Asisten Praktikum
            </option>
            <option value="mahasiswa" @selected(request('role') === 'mahasiswa')>
                Mahasiswa
            </option>
        </select>

        <select class="form-control" style="width:190px;" name="study_semester_id">
            <option value="">Semua semester</option>
            @foreach($studySemesters as $semester)
                <option value="{{ $semester->id }}" @selected((string) request('study_semester_id') === (string) $semester->id)>
                    {{ $semester->name }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width:190px;" name="status">
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

    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
        + Tambah Asisten / Mahasiswa
    </a>
</div>

<div class="table-card">
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
            @forelse($users as $user)
                @php
                    $currentRole = $user->roles->pluck('name')->first() ?: $user->role;

                    $roleLabel = match ($currentRole) {
                        'asisten' => 'Asisten Praktikum',
                        'mahasiswa' => 'Mahasiswa',
                        default => ucfirst($currentRole ?? '-'),
                    };

                    $isStudent = $currentRole === 'mahasiswa';
                    $isPendingStudent = $isStudent && ! $user->is_active;
                @endphp

                <tr>
                    <td>
                        <strong>{{ $user->name }}</strong>
                        <br>
                        <small>{{ $user->nim_nip ?? '-' }}</small>
                    </td>

                    <td>
                        {{ $user->email }}
                    </td>

                    <td>
                        <span class="badge badge-blue">
                            {{ $roleLabel }}
                        </span>
                    </td>

                    <td>
                        @if($isStudent)
                            {{ $user->studySemester?->name ?? '-' }}

                            @if($user->student_group)
                                <br>
                                <small>Kelas/Rombel {{ strtoupper($user->student_group) }}</small>
                            @endif
                        @else
                            -
                        @endif
                    </td>

                    <td>
                        @if($isPendingStudent)
                            <span class="badge badge-red">
                                Pending Verifikasi
                            </span>
                        @else
                            <span class="badge {{ $user->is_active ? 'badge-green' : 'badge-red' }}">
                                {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        @endif
                    </td>

                    <td class="actions-inline">
                        @if($isPendingStudent)
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
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        Belum ada asisten atau mahasiswa.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">
    {{ $users->links() }}
</div>
@endsection