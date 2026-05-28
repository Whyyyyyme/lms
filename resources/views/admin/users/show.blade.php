@extends('layouts.app', ['title' => 'Detail User'])
@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => $user->name,
    'description' => $user->email,
    'action' => '<a href="'.route('admin.users.edit', $user).'" class="rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Edit User</a>',
])
<div class="grid gap-6 lg:grid-cols-3">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm lg:col-span-2">
        <dl class="grid gap-4 sm:grid-cols-2">
            <div><dt class="text-sm text-slate-500">NIM/NIP</dt><dd class="font-semibold">{{ $user->nim_nip ?? '-' }}</dd></div>
            <div><dt class="text-sm text-slate-500">Role</dt><dd class="font-semibold">{{ $user->roles->pluck('name')->join(', ') ?: '-' }}</dd></div>
            <div><dt class="text-sm text-slate-500">Kelas Utama</dt><dd class="font-semibold">{{ $user->kelas?->name ?? '-' }}</dd></div>
            <div><dt class="text-sm text-slate-500">Status</dt><dd>@include('partials.badge', ['slot' => $user->is_active ? 'aktif' : 'nonaktif'])</dd></div>
        </dl>
    </section>
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="font-bold text-slate-950">Aksi</h2>
        <div class="mt-4 flex gap-3">
            <a href="{{ route('admin.users.index') }}" class="rounded-2xl border border-slate-300 px-4 py-2 text-sm font-semibold">Kembali</a>
            @include('partials.delete-button', ['action' => route('admin.users.destroy', $user)])
        </div>
    </section>
</div>
@endsection
