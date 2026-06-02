@extends('layouts.app', ['title' => 'Materi ' . $course->name])

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Mahasiswa',
    'title' => 'Materi ' . $course->name,
    'description' => 'Daftar materi praktikum berdasarkan mata kuliah yang dipilih.',
])

<div class="mb-5">
    <a href="{{ route('student.materials.index') }}"
       class="inline-flex rounded-2xl border bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
        ← Kembali ke Mata Kuliah
    </a>
</div>

<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
    @forelse($materials as $material)
        <a href="{{ route('student.materials.show', $material) }}"
           class="rounded-3xl border bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-50 hover:shadow-md">

            <div class="flex items-center justify-between gap-3">
                <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                    {{ strtoupper($material->type) }}
                </span>

                <span class="text-xs text-slate-400">
                    {{ $material->published_at?->format('d M Y') }}
                </span>
            </div>

            <h2 class="mt-4 font-bold text-slate-950">
                {{ $material->title }}
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                {{ $material->kelas?->name }}
            </p>

            <p class="mt-3 line-clamp-3 text-sm text-slate-600">
                {{ $material->description }}
            </p>

            <p class="mt-4 text-sm font-bold text-indigo-600">
                Buka materi →
            </p>
        </a>
    @empty
        <div class="md:col-span-2 lg:col-span-3">
            @include('partials.empty-state', [
                'title' => 'Belum ada materi',
                'description' => 'Belum ada materi yang dipublikasikan untuk mata kuliah ini.',
            ])
        </div>
    @endforelse
</div>

<div class="mt-5">
    {{ $materials->links() }}
</div>
@endsection
