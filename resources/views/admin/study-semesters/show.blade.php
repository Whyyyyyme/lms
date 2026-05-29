@extends('layouts.app')

@section('title', $studySemester->name)

@section('content')
    @include('partials.page-header', [
        'title' => $studySemester->name,
        'description' => 'Daftar matakuliah dan mahasiswa dalam semester ini.',
        'actions' => [['label' => 'Edit', 'href' => route('admin.semester.edit', $studySemester)]],
    ])

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold text-slate-900">Matakuliah dalam {{ $studySemester->name }}</h2>
            <div class="mt-4 space-y-3">
                @forelse ($studySemester->courses as $course)
                    <div class="rounded-xl bg-slate-50 p-3 text-sm">
                        <div class="font-semibold">{{ $course->name }}</div>
                        <div class="text-slate-500">{{ $course->code }} • {{ $course->sks }} SKS</div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada matakuliah.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="font-semibold text-slate-900">Mahasiswa {{ $studySemester->name }}</h2>
            <div class="mt-4 space-y-3">
                @forelse ($studySemester->students as $student)
                    <div class="rounded-xl bg-slate-50 p-3 text-sm">
                        <div class="font-semibold">{{ $student->name }}</div>
                        <div class="text-slate-500">{{ $student->nim_nip }} • {{ $student->email }}</div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada mahasiswa.</p>
                @endforelse
            </div>
        </section>
    </div>
@endsection
