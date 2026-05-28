@extends('layouts.app', ['title' => 'Dashboard Mahasiswa'])

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Mahasiswa',
    'title' => 'Dashboard Mahasiswa',
    'description' => 'Pantau materi, tugas, nilai, absensi, dan pengumuman praktikum.',
])

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
    @foreach ($statistics as $label => $value)
        @include('partials.stat-card', ['label' => ucwords(str_replace('_', ' ', $label)), 'value' => $value])
    @endforeach
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-3">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-950">Materi Terbaru</h2>
        <div class="mt-4 space-y-3">
            @forelse ($latestMaterials as $material)
                <a href="{{ route('student.materials.show', $material) }}" class="block rounded-2xl bg-slate-50 p-4 hover:bg-indigo-50">
                    <p class="font-semibold text-slate-900">{{ $material->title }}</p>
                    <p class="text-sm text-slate-500">{{ $material->kelas?->course?->name }}</p>
                </a>
            @empty
                @include('partials.empty-state', ['title' => 'Belum ada materi'])
            @endforelse
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-950">Deadline Terdekat</h2>
        <div class="mt-4 space-y-3">
            @forelse ($upcomingAssignments as $assignment)
                <a href="{{ route('student.assignments.show', $assignment) }}" class="block rounded-2xl bg-slate-50 p-4 hover:bg-indigo-50">
                    <p class="font-semibold text-slate-900">{{ $assignment->title }}</p>
                    <p class="text-sm text-slate-500">Deadline {{ $assignment->deadline?->format('d M Y H:i') }}</p>
                    <p class="mt-1 text-xs font-semibold {{ $assignment->submissions->isNotEmpty() ? 'text-emerald-600' : 'text-amber-600' }}">
                        {{ $assignment->submissions->isNotEmpty() ? 'Sudah dikumpulkan' : 'Belum dikumpulkan' }}
                    </p>
                </a>
            @empty
                @include('partials.empty-state', ['title' => 'Tidak ada deadline dekat'])
            @endforelse
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-950">Pengumuman</h2>
        <div class="mt-4 space-y-3">
            @forelse ($announcements as $announcement)
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="font-semibold text-slate-900">{{ $announcement->title }}</p>
                    <p class="mt-1 line-clamp-2 text-sm text-slate-500">{{ $announcement->content }}</p>
                </div>
            @empty
                @include('partials.empty-state', ['title' => 'Belum ada pengumuman'])
            @endforelse
        </div>
    </section>
</div>
@endsection
