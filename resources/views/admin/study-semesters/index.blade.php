@extends('layouts.app')

@section('title', 'Semester Mahasiswa')

@section('content')
    @include('partials.page-header', [
        'title' => 'Semester Mahasiswa',
        'description' => 'Kelola semester mahasiswa. Di dalam setiap semester terdapat beberapa matakuliah praktikum.',
        'actions' => [['label' => 'Tambah Semester', 'href' => route('admin.semester.create')]],
    ])

    <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-4 py-3">Level</th>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Matakuliah</th>
                    <th class="px-4 py-3">Mahasiswa</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($studySemesters as $semester)
                    <tr>
                        <td class="px-4 py-3 font-semibold">{{ $semester->level }}</td>
                        <td class="px-4 py-3">{{ $semester->name }}</td>
                        <td class="px-4 py-3">{{ $semester->courses_count }}</td>
                        <td class="px-4 py-3">{{ $semester->students_count }}</td>
                        <td class="px-4 py-3">{{ $semester->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                        <td class="px-4 py-3 text-right">
                            <a class="text-indigo-600 hover:underline" href="{{ route('admin.semester.show', $semester) }}">Detail</a>
                            <a class="ml-3 text-amber-600 hover:underline" href="{{ route('admin.semester.edit', $semester) }}">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">Belum ada semester.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $studySemesters->links() }}</div>
@endsection
