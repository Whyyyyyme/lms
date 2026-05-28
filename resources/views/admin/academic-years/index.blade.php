@extends('layouts.app', ['title' => 'Tahun Akademik'])
@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Tahun Akademik',
    'description' => 'Kelola periode akademik dan semester aktif.',
    'action' => '<a href="'.route('admin.tahun-akademik.create').'" class="rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Tambah Tahun Akademik</a>',
])
<div class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
    <table class="min-w-full divide-y divide-slate-200 text-sm">
        <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500"><tr><th class="px-5 py-3">Tahun</th><th class="px-5 py-3">Semester</th><th class="px-5 py-3">Matakuliah</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Aksi</th></tr></thead>
        <tbody class="divide-y divide-slate-100">
            @forelse ($academicYears as $academicYear)
                <tr>
                    <td class="px-5 py-4 font-semibold">{{ $academicYear->year }}</td>
                    <td class="px-5 py-4">{{ ucfirst($academicYear->semester) }}</td>
                    <td class="px-5 py-4">{{ $academicYear->courses_count }}</td>
                    <td class="px-5 py-4">@include('partials.badge', ['slot' => $academicYear->is_active ? 'aktif' : 'nonaktif'])</td>
                    <td class="px-5 py-4 text-right"><a href="{{ route('admin.tahun-akademik.show', $academicYear) }}" class="font-semibold text-indigo-600">Detail</a><a href="{{ route('admin.tahun-akademik.edit', $academicYear) }}" class="ml-3 font-semibold text-slate-600">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-5 py-10">@include('partials.empty-state')</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $academicYears->links() }}</div>
@endsection
