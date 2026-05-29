@extends('layouts.app')
@section('title', 'Kelola User')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Kelola User', 'description' => 'Tambah, edit, hapus, dan atur role pengguna LMS.'])
<div class="toolbar">
    <form method="GET" class="actions-inline">
        <input class="form-control" style="width:260px;" type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama/email/NIM">
        <select class="form-control" style="width:170px;" name="role">
            <option value="">Semua role</option>
            @foreach(['admin' => 'Admin', 'asisten' => 'Asisten', 'mahasiswa' => 'Mahasiswa'] as $value => $label)
                <option value="{{ $value }}" @selected(request('role') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <button class="btn" type="submit">Filter</button>
    </form>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary">+ Tambah User</a>
</div>
<div class="table-card">
    <table>
        <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead>
        <tbody>
            @forelse($users as $user)
                <tr>
                    <td><strong>{{ $user->name }}</strong><br><small>{{ $user->nim_nip ?? '-' }}</small></td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->roles->pluck('name')->join(', ') ?: ($user->role ?? '-') }}</td>
                    <td><span class="badge {{ $user->is_active ? 'badge-green' : 'badge-red' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                    <td class="actions-inline">
                        <a class="btn btn-sm" href="{{ route('admin.users.show', $user) }}">Detail</a>
                        <a class="btn btn-sm" href="{{ route('admin.users.edit', $user) }}">Edit</a>
                        @include('partials.delete-button', ['action' => route('admin.users.destroy', $user)])
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Belum ada user.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div style="margin-top:16px;">{{ $users->links() }}</div>
@endsection
