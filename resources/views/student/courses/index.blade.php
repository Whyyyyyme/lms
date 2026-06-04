@extends('layouts.app', ['title' => 'Mata Kuliah Saya'])

@section('title', 'Mata Kuliah Saya')
@section('page_title', 'Mata Kuliah Saya')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Mahasiswa',
    'title' => 'Mata Kuliah Saya',
    'description' => 'Pilih mata kuliah atau kelas praktikum terlebih dahulu, lalu masuk ke materi, tugas, absensi, pengumuman, dan nilai kelas tersebut.',
])

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
    @include('partials.stat-card', ['label' => 'Kelas Aktif', 'value' => $statistics['total_classes'] ?? 0, 'icon' => '📚'])
    @include('partials.stat-card', ['label' => 'Materi', 'value' => $statistics['total_materials'] ?? 0, 'icon' => '📘'])
    @include('partials.stat-card', ['label' => 'Tugas', 'value' => $statistics['total_assignments'] ?? 0, 'icon' => '📝'])
    @include('partials.stat-card', ['label' => 'Belum Submit', 'value' => $statistics['pending_assignments'] ?? 0, 'icon' => '⏳'])
    @include('partials.stat-card', ['label' => 'Absensi Buka', 'value' => $statistics['open_attendances'] ?? 0, 'icon' => '✅'])
</div>

<div class="grid gap-6 xl:grid-cols-3">
    <section class="xl:col-span-2">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-black text-slate-950">Daftar Mata Kuliah / Kelas</h2>
                <p class="mt-1 text-sm text-slate-500">Data diambil dari semester dan rombel mahasiswa yang sedang login.</p>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            @forelse($classes as $class)
                @php
                    $course = $class->course;
                    $nextAssignment = $class->next_assignment ?? null;
                    $averageScore = $class->average_score;
                @endphp

                <a href="{{ route('student.courses.show', $class) }}"
                   class="rounded-3xl border bg-white p-5 shadow-sm transition hover:border-indigo-300 hover:bg-indigo-50 hover:shadow-md">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <span class="rounded-full bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700">
                                {{ $course?->code ?? 'Mata Kuliah' }}
                            </span>

                            <h3 class="mt-4 text-lg font-black text-slate-950">
                                {{ $course?->name ?? 'Mata kuliah tidak ditemukan' }}
                            </h3>

                            <p class="mt-1 text-sm text-slate-500">
                                {{ $class->name }}
                                @if($class->room)
                                    · Ruang {{ $class->room }}
                                @endif
                            </p>
                        </div>

                        @if(($class->open_attendances_count ?? 0) > 0)
                            <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
                                Absensi Buka
                            </span>
                        @endif
                    </div>

                    <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <p class="text-lg font-black text-slate-950">{{ $class->published_materials_count ?? 0 }}</p>
                            <p class="text-[11px] font-semibold uppercase text-slate-500">Materi</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <p class="text-lg font-black text-slate-950">{{ $class->published_assignments_count ?? 0 }}</p>
                            <p class="text-[11px] font-semibold uppercase text-slate-500">Tugas</p>
                        </div>
                        <div class="rounded-2xl bg-slate-50 p-3">
                            <p class="text-lg font-black text-slate-950">{{ $averageScore !== null ? number_format((float) $averageScore, 1) : '-' }}</p>
                            <p class="text-[11px] font-semibold uppercase text-slate-500">Nilai</p>
                        </div>
                    </div>

                    <div class="mt-4 space-y-1 text-sm text-slate-500">
                        @if($course?->studySemester)
                            <p>Semester: {{ $course->studySemester->name }}</p>
                        @endif

                        @if($course?->academicYear)
                            <p>Tahun akademik: {{ $course->academicYear->name }}</p>
                        @endif

                        @if($class->assistant)
                            <p>Asisten: {{ $class->assistant->name }}</p>
                        @endif

                        @if($class->schedule)
                            <p>Jadwal: {{ $class->schedule }}</p>
                        @endif
                    </div>

                    @if($nextAssignment)
                        <div class="mt-4 rounded-2xl border border-amber-100 bg-amber-50 p-3">
                            <p class="text-xs font-bold uppercase text-amber-700">Tugas berikutnya</p>
                            <p class="mt-1 font-bold text-slate-950">{{ $nextAssignment->title }}</p>
                            <p class="mt-1 text-xs text-slate-500">
                                Deadline: {{ $nextAssignment->deadline?->format('d M Y H:i') ?? '-' }}
                            </p>
                        </div>
                    @elseif(($class->latest_material_at ?? null))
                        <p class="mt-4 text-xs text-slate-400">
                            Materi terakhir: {{ \Carbon\Carbon::parse($class->latest_material_at)->format('d M Y H:i') }}
                        </p>
                    @endif

                    <p class="mt-4 text-sm font-bold text-indigo-600">Masuk kelas →</p>
                </a>
            @empty
                <div class="md:col-span-2">
                    @include('partials.empty-state', [
                        'title' => 'Belum ada mata kuliah',
                        'description' => 'Mata kuliah akan muncul jika akun mahasiswa sudah memiliki semester dan rombel yang sesuai dengan kelas praktikum aktif.',
                        'icon' => '📚',
                    ])
                </div>
            @endforelse
        </div>
    </section>

    <aside class="space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-black text-slate-950">Deadline Terdekat</h3>

            <div class="mt-4 space-y-3">
                @forelse($upcomingAssignments as $assignment)
                    @php
                        $submitted = $assignment->relationLoaded('submissions') && $assignment->submissions->isNotEmpty();
                    @endphp
                    <a href="{{ route('student.assignments.show', $assignment) }}"
                       class="block rounded-2xl border border-slate-200 bg-slate-50 p-4 hover:bg-indigo-50">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold text-slate-950">{{ $assignment->title }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $assignment->kelas?->course?->name ?? 'Mata Kuliah' }}</p>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $submitted ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                {{ $submitted ? 'Sudah' : 'Belum' }}
                            </span>
                        </div>
                        <p class="mt-2 text-xs text-slate-500">{{ $assignment->deadline?->format('d M Y H:i') ?? '-' }}</p>
                    </a>
                @empty
                    <p class="text-sm text-slate-500">Belum ada deadline tugas terdekat.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-lg font-black text-slate-950">Absensi Sedang Dibuka</h3>

            <div class="mt-4 space-y-3">
                @forelse($openAttendances as $attendance)
                    @php
                        $record = $attendance->records->first();
                        $alreadyPresent = $record?->status === 'hadir';
                    @endphp

                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-bold text-slate-950">{{ $attendance->kelas?->course?->name ?? 'Mata Kuliah' }}</p>
                        <p class="mt-1 text-sm text-slate-500">Ditutup: {{ $attendance->closed_at?->format('d M Y H:i') ?? '-' }}</p>

                        @if($alreadyPresent)
                            <span class="mt-3 inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">
                                Sudah check-in
                            </span>
                        @else
                            <form action="{{ route('student.attendances.check-in', $attendance) }}" method="POST" class="mt-3">
                                @csrf
                                <button type="submit" class="btn btn-primary btn-sm">Check-in</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Tidak ada absensi yang sedang dibuka.</p>
                @endforelse
            </div>
        </section>
    </aside>
</div>
@endsection
