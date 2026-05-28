@extends('layouts.app', ['title' => 'Dashboard Asisten'])

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Asisten Praktikum',
    'title' => 'Dashboard Asisten',
    'description' => 'Kelola materi, tugas, absensi, dan pengumuman kelas praktikum.',
])

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ($statistics as $label => $value)
        @include('partials.stat-card', ['label' => ucwords(str_replace('_', ' ', $label)), 'value' => $value])
    @endforeach
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-2">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-950">Kelas Saya</h2>
        <div class="mt-4 space-y-3">
            @forelse ($classes as $class)
                <div class="rounded-2xl bg-slate-50 p-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $class->name }}</p>
                            <p class="text-sm text-slate-500">{{ $class->course?->name }} · {{ $class->schedule ?? 'Jadwal belum diatur' }}</p>
                        </div>
                        <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">{{ $class->students_count }} mahasiswa</span>
                    </div>
                </div>
            @empty
                @include('partials.empty-state', ['title' => 'Belum ada kelas'])
            @endforelse
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-slate-950">Submission Terbaru</h2>
            <a href="{{ route('assistant.submissions.index') }}" class="text-sm font-semibold text-indigo-600">Lihat semua</a>
        </div>
        <div class="mt-4 space-y-3">
            @forelse ($latestSubmissions as $submission)
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="font-semibold text-slate-900">{{ $submission->student?->name }}</p>
                    <p class="text-sm text-slate-500">{{ $submission->assignment?->title }} · {{ $submission->submitted_at?->format('d M Y H:i') }}</p>
                </div>
            @empty
                @include('partials.empty-state', ['title' => 'Belum ada submission'])
            @endforelse
        </div>
    </section>
</div>
@endsection
