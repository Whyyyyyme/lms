@extends('layouts.app', ['title' => 'Kelola User'])

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Kelola User',
    'description' => 'Tambah, edit, dan hapus akun admin, asisten, serta mahasiswa.',
    'action' => '<a href="'.route('admin.users.create').'" class="rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">Tambah User</a>',
])

<form method="GET" class="mb-5 grid gap-3 rounded-3xl border border-slate-200 bg-white p-4 shadow-sm md:grid-cols-3">
    <input name="search" value="{{ request('search') }}" placeholder="Cari nama/email/NIM" class="rounded-2xl border-slate-300">
    <select name="role" class="rounded-2xl border-slate-300">
        <option value="">Semua role</option>
        @foreach (['admin', 'asisten', 'mahasiswa'] as $role)
            <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>
        @endforeach
    </select>
    <button class="rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Filter</button>
</form>

<div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-5 py-3">User</th>
                <th class="px-5 py-3">Role</th>
                <th class="px-5 py-3">Kelas</th>
                <th class="px-5 py-3">Status</th>
                <th class="px-5 py-3 text-right">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($users as $user)
                <tr>
                    <td class="px-5 py-4">
                        <p class="font-semibold text-slate-900">{{ $user->name }}</p>
                        <p class="text-slate-500">{{ $user->email }} · {{ $user->nim_nip ?? '-' }}</p>
                    </td>
                    <td class="px-5 py-4">{{ $user->roles->pluck('name')->map(fn($r) => ucfirst($r))->join(', ') ?: '-' }}</td>
                    <td class="px-5 py-4">{{ $user->kelas?->name ?? '-' }}</td>
                    <td class="px-5 py-4">@include('partials.badge', ['slot' => $user->is_active ? 'aktif' : 'nonaktif'])</td>
                    <td class="px-5 py-4 text-right">
                        <a href="{{ route('admin.users.show', $user) }}" class="font-semibold text-indigo-600">Detail</a>
                        <a href="{{ route('admin.users.edit', $user) }}" class="ml-3 font-semibold text-slate-600">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-10">@include('partials.empty-state')</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $users->links() }}</div>
@endsection
