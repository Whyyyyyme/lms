@extends('layouts.app', ['title' => 'Detail Tahun Akademik'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => $academicYear->year.' - '.ucfirst($academicYear->semester)])
<section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-5 flex justify-end gap-3"><a href="{{ route('admin.tahun-akademik.edit', $academicYear) }}" class="rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Edit</a>@include('partials.delete-button', ['action' => route('admin.tahun-akademik.destroy', $academicYear)])</div>
    <h2 class="text-lg font-bold">Daftar Matakuliah</h2>
    <div class="mt-4 grid gap-3 md:grid-cols-2">
        @forelse ($academicYear->courses as $course)
            <div class="rounded-2xl bg-slate-50 p-4"><p class="font-semibold">{{ $course->name }}</p><p class="text-sm text-slate-500">{{ $course->code }} · {{ $course->classes->count() }} kelas</p></div>
        @empty
            @include('partials.empty-state', ['title' => 'Belum ada matakuliah'])
        @endforelse
    </div>
</section>
@endsection
