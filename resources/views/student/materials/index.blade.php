@extends('layouts.app', ['title' => 'Materi Praktikum'])

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Mahasiswa',
    'title' => 'Materi Praktikum',
    'description' => 'Pilih mata kuliah untuk melihat materi praktikum.',
])

<div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
    @forelse($courses as $course)
        <a href="{{ route('student.materials.course', $course) }}"
           class="rounded-3xl border bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-50 hover:shadow-md">

            <div class="flex items-start justify-between gap-3">
                <div>
                    <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                        {{ $course->code ?? 'Mata Kuliah' }}
                    </span>

                    <h2 class="mt-4 font-bold text-slate-950">
                        {{ $course->name }}
                    </h2>
                </div>

                <div class="rounded-2xl bg-slate-100 px-3 py-2 text-center">
                    <p class="text-lg font-black text-slate-900">
                        {{ $course->materials_count }}
                    </p>
                    <p class="text-[11px] font-semibold uppercase text-slate-500">
                        Materi
                    </p>
                </div>
            </div>

            <div class="mt-4 space-y-1 text-sm text-slate-500">
                @if($course->studySemester)
                    <p>Semester: {{ $course->studySemester->name }}</p>
                @endif

                @if($course->academicYear)
                    <p>Tahun Akademik: {{ $course->academicYear->name }}</p>
                @endif

                @if($course->latest_material_at)
                    <p>
                        Update terakhir:
                        {{ \Carbon\Carbon::parse($course->latest_material_at)->format('d M Y H:i') }}
                    </p>
                @else
                    <p>Belum ada materi dipublikasikan.</p>
                @endif
            </div>

            <p class="mt-4 text-sm font-bold text-indigo-600">
                Lihat materi →
            </p>
        </a>
    @empty
        <div class="md:col-span-2 lg:col-span-3">
            @include('partials.empty-state', [
                'title' => 'Belum ada mata kuliah',
                'description' => 'Mata kuliah akan muncul sesuai semester mahasiswa.',
            ])
        </div>
    @endforelse
</div>
@endsection
