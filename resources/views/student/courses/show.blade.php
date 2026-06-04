@extends('layouts.app', ['title' => $class->course?->name ?? 'Detail Mata Kuliah'])

@section('title', $class->course?->name ?? 'Detail Mata Kuliah')
@section('page_title', $class->course?->name ?? 'Detail Mata Kuliah')

@section('content')
@php
    $course = $class->course;
    $timezone = config('app.timezone', 'Asia/Jakarta');
@endphp

<div class="mb-5">
    <a href="{{ route('student.courses.index') }}" class="inline-flex rounded-2xl border bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
        ← Kembali ke Mata Kuliah Saya
    </a>
</div>

<section class="mb-6 rounded-3xl border border-slate-200 bg-slate-950 p-6 text-white shadow-sm">
    <div class="flex flex-col justify-between gap-5 lg:flex-row lg:items-end">
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-indigo-500 px-3 py-1 text-xs font-bold text-white">
                    {{ $course?->code ?? 'Mata Kuliah' }}
                </span>

                @if($course?->studySemester)
                    <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-slate-200">
                        {{ $course->studySemester->name }}
                    </span>
                @endif

                @if($course?->academicYear)
                    <span class="rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-slate-200">
                        {{ $course->academicYear->name }}
                    </span>
                @endif
            </div>

            <h1 class="mt-5 text-3xl font-black tracking-tight md:text-4xl">
                {{ $course?->name ?? 'Mata kuliah tidak ditemukan' }}
            </h1>

            <div class="mt-4 flex flex-wrap gap-4 text-sm text-slate-300">
                <span>Kelas: <strong class="text-white">{{ $class->name }}</strong></span>

                @if($class->assistant)
                    <span>Asisten: <strong class="text-white">{{ $class->assistant->name }}</strong></span>
                @endif

                @if($class->schedule)
                    <span>Jadwal: <strong class="text-white">{{ $class->schedule }}</strong></span>
                @endif

                @if($class->room)
                    <span>Ruang: <strong class="text-white">{{ $class->room }}</strong></span>
                @endif
            </div>
        </div>

        <div class="rounded-3xl bg-white/10 p-4 text-sm text-slate-200">
            <p class="font-bold text-white">Progress Tugas</p>
            <p class="mt-1 text-3xl font-black text-white">{{ $summary['progress'] ?? 0 }}%</p>
            <p class="mt-1">{{ $summary['submitted_assignments'] ?? 0 }} dari {{ $summary['total_assignments'] ?? 0 }} tugas dikumpulkan</p>
        </div>
    </div>
</section>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
    @include('partials.stat-card', ['label' => 'Materi', 'value' => $summary['total_materials'] ?? 0, 'icon' => '📘'])
    @include('partials.stat-card', ['label' => 'Tugas', 'value' => $summary['total_assignments'] ?? 0, 'icon' => '📝'])
    @include('partials.stat-card', ['label' => 'Belum Submit', 'value' => $summary['pending_assignments'] ?? 0, 'icon' => '⏳'])
    @include('partials.stat-card', ['label' => 'Absensi Buka', 'value' => $summary['open_attendances'] ?? 0, 'icon' => '✅'])
    @include('partials.stat-card', ['label' => 'Rata-rata Nilai', 'value' => ($summary['average_score'] ?? null) !== null ? number_format((float) $summary['average_score'], 1) : '-', 'icon' => '⭐'])
</div>

<div class="grid gap-6 xl:grid-cols-3">
    <section id="materi" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-2">
        <div class="mb-4 flex items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-black text-slate-950">Materi Pembelajaran</h2>
                <p class="mt-1 text-sm text-slate-500">Materi yang sudah dipublikasikan oleh asisten untuk kelas ini.</p>
            </div>
        </div>

        <div class="space-y-3">
            @forelse($materials as $material)
                <a href="{{ route('student.materials.show', $material) }}"
                   class="block rounded-2xl border border-slate-200 bg-slate-50 p-4 hover:bg-indigo-50">
                    <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full bg-indigo-100 px-2.5 py-1 text-xs font-bold text-indigo-700">
                                    {{ strtoupper($material->type ?? 'materi') }}
                                </span>
                                <span class="text-xs text-slate-400">
                                    {{ $material->published_at?->timezone($timezone)->format('d M Y H:i') ?? '-' }} WIB
                                </span>
                            </div>

                            <h3 class="mt-3 font-bold text-slate-950">{{ $material->title }}</h3>

                            @if($material->description)
                                <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ $material->description }}</p>
                            @endif
                        </div>

                        <span class="text-sm font-bold text-indigo-600">Buka materi →</span>
                    </div>
                </a>
            @empty
                @include('partials.empty-state', [
                    'title' => 'Belum ada materi',
                    'description' => 'Materi untuk kelas ini akan tampil setelah dipublikasikan oleh asisten.',
                    'icon' => '📘',
                ])
            @endforelse
        </div>

        <div class="mt-5">
            {{ $materials->links() }}
        </div>
    </section>

    <aside class="space-y-6">
        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-950">Pengumuman</h2>

            <div class="mt-4 space-y-3">
                @forelse($announcements as $announcement)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-bold text-slate-950">{{ $announcement->title }}</p>
                        <p class="mt-2 line-clamp-4 text-sm leading-6 text-slate-600">{{ $announcement->content }}</p>
                        <p class="mt-2 text-xs text-slate-400">
                            {{ $announcement->created_at?->timezone($timezone)->format('d M Y H:i') ?? '-' }} WIB
                        </p>
                    </div>
                @empty
                    <p class="text-sm text-slate-500">Belum ada pengumuman untuk kelas ini.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-xl font-black text-slate-950">Navigasi Kelas</h2>
            <div class="mt-4 grid gap-2">
                <a href="#materi" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Materi</a>
                <a href="#tugas" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Tugas</a>
                <a href="#absensi" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Absensi</a>
                <a href="{{ route('student.schedule.index', ['mata_kuliah' => $course?->id]) }}" class="rounded-2xl border border-slate-200 px-4 py-3 text-sm font-bold text-slate-700 hover:bg-slate-50">Jadwal/Kalender</a>
            </div>
        </section>
    </aside>
</div>

<section id="tugas" class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-black text-slate-950">Tugas Kelas</h2>
            <p class="mt-1 text-sm text-slate-500">Upload submission dari halaman detail tugas.</p>
        </div>
    </div>

    <div class="space-y-3">
        @forelse($assignments as $assignment)
            @php
                $submission = $assignment->submissions->first();
                $submitted = $submission !== null;
                $isExpired = $assignment->deadline && $assignment->deadline->lessThan(now());
            @endphp

            <a href="{{ route('student.assignments.show', $assignment) }}"
               class="block rounded-2xl border border-slate-200 bg-slate-50 p-4 hover:bg-indigo-50">
                <div class="flex flex-col justify-between gap-3 md:flex-row md:items-start">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $submitted ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                {{ $submitted ? 'Sudah dikumpulkan' : 'Belum dikumpulkan' }}
                            </span>

                            @if($isExpired)
                                <span class="rounded-full bg-rose-100 px-2.5 py-1 text-xs font-bold text-rose-700">Deadline lewat</span>
                            @endif
                        </div>

                        <h3 class="mt-3 font-bold text-slate-950">{{ $assignment->title }}</h3>

                        @if($assignment->description)
                            <p class="mt-2 line-clamp-2 text-sm text-slate-600">{{ $assignment->description }}</p>
                        @endif

                        <p class="mt-2 text-xs font-semibold text-slate-500">
                            Deadline: {{ $assignment->deadline?->timezone($timezone)->format('d M Y H:i') ?? '-' }} WIB
                        </p>
                    </div>

                    <span class="text-sm font-bold text-indigo-600">Buka tugas →</span>
                </div>
            </a>
        @empty
            @include('partials.empty-state', [
                'title' => 'Belum ada tugas',
                'description' => 'Tugas yang dipublikasikan asisten akan tampil di bagian ini.',
                'icon' => '📝',
            ])
        @endforelse
    </div>

    <div class="mt-5">
        {{ $assignments->links() }}
    </div>
</section>

<section id="absensi" class="mt-6 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="mb-4 flex items-center justify-between gap-3">
        <div>
            <h2 class="text-xl font-black text-slate-950">Absensi Kelas</h2>
            <p class="mt-1 text-sm text-slate-500">Check-in hanya tersedia saat waktu absensi sedang dibuka.</p>
        </div>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Waktu Absensi</th>
                    <th>Status Sesi</th>
                    <th>Status Kamu</th>
                    <th>Check-in</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                    @php
                        $record = $attendance->records->first();
                        $studentStatus = $record?->status ?? 'alpha';
                        $studentStatusLabel = match ($studentStatus) {
                            'hadir' => 'Hadir',
                            'izin' => 'Izin',
                            default => 'Alpha',
                        };
                        $studentStatusClass = match ($studentStatus) {
                            'hadir' => 'badge-green',
                            'izin' => 'badge-blue',
                            default => 'badge-red',
                        };
                        $sessionStatus = method_exists($attendance, 'statusLabel') ? $attendance->statusLabel() : ($attendance->is_open ? 'Sedang Dibuka' : 'Ditutup');
                        $sessionStatusClass = method_exists($attendance, 'statusBadgeClass') ? $attendance->statusBadgeClass() : ($attendance->is_open ? 'badge-green' : 'badge-red');
                        $canCheckIn = method_exists($attendance, 'isWithinOpenWindow')
                            ? $attendance->isWithinOpenWindow() && $studentStatus === 'alpha'
                            : $attendance->is_open && $studentStatus === 'alpha';
                    @endphp

                    <tr>
                        <td>
                            <strong>Dibuka:</strong>
                            {{ $attendance->opened_at?->timezone($timezone)->format('d M Y H:i') ?? '-' }} WIB
                            <br>
                            <strong>Ditutup:</strong>
                            {{ $attendance->closed_at?->timezone($timezone)->format('d M Y H:i') ?? '-' }} WIB
                        </td>
                        <td>
                            <span class="badge {{ $sessionStatusClass }}">{{ $sessionStatus }}</span>
                        </td>
                        <td>
                            <span class="badge {{ $studentStatusClass }}">{{ $studentStatusLabel }}</span>
                        </td>
                        <td>
                            {{ $record?->checked_at?->timezone($timezone)->format('d M Y H:i') ?? '-' }} WIB
                        </td>
                        <td>
                            @if($canCheckIn)
                                <form action="{{ route('student.attendances.check-in', $attendance) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm">Check-in</button>
                                </form>
                            @elseif($studentStatus === 'hadir')
                                <span class="badge badge-green">Sudah check-in</span>
                            @elseif($studentStatus === 'izin')
                                <span class="badge badge-blue">Izin</span>
                            @else
                                <span class="badge">Tidak tersedia</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Belum ada sesi absensi untuk kelas ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5">
        {{ $attendances->links() }}
    </div>
</section>
@endsection
