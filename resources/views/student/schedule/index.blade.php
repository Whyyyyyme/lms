@extends('layouts.app', ['title' => 'Jadwal Praktikum'])

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Mahasiswa',
    'title' => 'Jadwal Praktikum',
    'description' => 'Kalender aktivitas praktikum, deadline tugas, dan sesi absensi.',
])

@php
    $previousQuery = array_filter([
        'bulan' => $previousMonth->format('Y-m'),
        'mata_kuliah' => $selectedCourseId,
    ]);

    $nextQuery = array_filter([
        'bulan' => $nextMonth->format('Y-m'),
        'mata_kuliah' => $selectedCourseId,
    ]);

    $todayQuery = array_filter([
        'bulan' => now()->format('Y-m'),
        'mata_kuliah' => $selectedCourseId,
    ]);

    $dayNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
@endphp

<div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
    <section class="rounded-3xl border bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-xl font-black text-slate-950">
                    {{ $month->translatedFormat('F Y') }}
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Praktikum, tugas, dan absensi ditampilkan dalam satu kalender.
                </p>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <form method="GET" action="{{ route('student.schedule.index') }}">
                    <input type="hidden" name="bulan" value="{{ $month->format('Y-m') }}">

                    <select name="mata_kuliah"
                            onchange="this.form.submit()"
                            class="w-full rounded-2xl border-slate-200 text-sm font-semibold shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:w-56">
                        <option value="">Semua mata kuliah</option>

                        @foreach($courses as $course)
                            <option value="{{ $course->id }}" @selected((int) $selectedCourseId === (int) $course->id)>
                                {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <div class="flex items-center gap-2">
                    <a href="{{ route('student.schedule.index', $previousQuery) }}"
                       class="rounded-2xl border bg-white px-3 py-2 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50">
                        ←
                    </a>

                    <a href="{{ route('student.schedule.index', $todayQuery) }}"
                       class="rounded-2xl border bg-white px-3 py-2 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50">
                        Hari ini
                    </a>

                    <a href="{{ route('student.schedule.index', $nextQuery) }}"
                       class="rounded-2xl border bg-white px-3 py-2 text-sm font-bold text-slate-700 shadow-sm hover:bg-slate-50">
                        →
                    </a>
                </div>
            </div>
        </div>

        <div class="mt-5 flex flex-wrap gap-2 text-xs font-bold">
            <span class="rounded-full border border-blue-200 bg-blue-50 px-3 py-1 text-blue-700">
                Praktikum
            </span>
            <span class="rounded-full border border-amber-200 bg-amber-50 px-3 py-1 text-amber-700">
                Tugas
            </span>
            <span class="rounded-full border border-emerald-200 bg-emerald-50 px-3 py-1 text-emerald-700">
                Absensi
            </span>
        </div>

        <div class="mt-6 overflow-x-auto">
            <div class="min-w-[900px] overflow-hidden rounded-3xl border">
                <div class="grid grid-cols-7 bg-slate-50">
                    @foreach($dayNames as $dayName)
                        <div class="border-r px-3 py-3 text-center text-sm font-black text-slate-700 last:border-r-0">
                            {{ $dayName }}
                        </div>
                    @endforeach
                </div>

                @foreach($weeks as $week)
                    <div class="grid grid-cols-7 border-t">
                        @foreach($week as $day)
                            <div class="min-h-36 border-r p-3 last:border-r-0 {{ $day['is_current_month'] ? 'bg-white' : 'bg-slate-50 text-slate-400' }}">
                                <div class="mb-2 flex items-center justify-between">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-full text-sm font-black
                                        {{ $day['is_today'] ? 'bg-indigo-600 text-white' : 'text-slate-700' }}">
                                        {{ $day['date']->day }}
                                    </span>

                                    @if($day['events']->count() > 0)
                                        <span class="rounded-full bg-slate-100 px-2 py-1 text-[10px] font-bold text-slate-500">
                                            {{ $day['events']->count() }}
                                        </span>
                                    @endif
                                </div>

                                <div class="space-y-1.5">
                                    @foreach($day['events']->take(4) as $event)
                                        @php
                                            $eventClass = match ($event['variant']) {
                                                'praktikum' => 'border-blue-200 bg-blue-50 text-blue-700 hover:bg-blue-100',
                                                'tugas' => 'border-amber-200 bg-amber-50 text-amber-700 hover:bg-amber-100',
                                                'absensi' => 'border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100',
                                                default => 'border-slate-200 bg-slate-50 text-slate-700 hover:bg-slate-100',
                                            };
                                        @endphp

                                        <a href="{{ $event['url'] ?: '#' }}"
                                           class="block rounded-xl border px-2 py-1.5 text-[11px] font-bold leading-snug transition {{ $eventClass }}"
                                           title="{{ $event['title'] }} - {{ $event['subtitle'] }}">
                                            <div class="truncate">
                                                @if($event['time'])
                                                    <span>{{ $event['time'] }}</span>
                                                    <span> · </span>
                                                @endif

                                                <span>{{ $event['title'] }}</span>
                                            </div>

                                            <div class="truncate text-[10px] font-semibold opacity-80">
                                                {{ $event['badge'] }} · {{ $event['subtitle'] }}
                                            </div>
                                        </a>
                                    @endforeach

                                    @if($day['events']->count() > 4)
                                        <div class="rounded-xl bg-slate-100 px-2 py-1 text-[11px] font-bold text-slate-500">
                                            +{{ $day['events']->count() - 4 }} aktivitas lainnya
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <aside class="space-y-4">
        <section class="rounded-3xl border bg-white p-5 shadow-sm">
            <h2 class="font-black text-slate-950">
                Aktivitas Terdekat
            </h2>

            <div class="mt-4 space-y-3">
                @forelse($upcomingEvents as $event)
                    @php
                        $eventClass = match ($event['variant']) {
                            'praktikum' => 'border-blue-200 bg-blue-50 text-blue-700',
                            'tugas' => 'border-amber-200 bg-amber-50 text-amber-700',
                            'absensi' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
                            default => 'border-slate-200 bg-slate-50 text-slate-700',
                        };
                    @endphp

                    <a href="{{ $event['url'] ?: '#' }}"
                       class="block rounded-2xl border p-4 transition hover:shadow-sm {{ $eventClass }}">
                        <div class="flex items-center justify-between gap-2">
                            <span class="rounded-full bg-white/70 px-2.5 py-1 text-[10px] font-black uppercase">
                                {{ $event['badge'] }}
                            </span>

                            <span class="text-xs font-bold">
                                {{ \Carbon\Carbon::parse($event['date'])->format('d M') }}
                            </span>
                        </div>

                        <h3 class="mt-3 font-black">
                            {{ $event['title'] }}
                        </h3>

                        <p class="mt-1 text-sm font-semibold opacity-80">
                            @if($event['time'])
                                {{ $event['time'] }} ·
                            @endif

                            {{ $event['subtitle'] }}
                        </p>
                    </a>
                @empty
                    @include('partials.empty-state', [
                        'title' => 'Belum ada aktivitas',
                        'description' => 'Aktivitas praktikum, tugas, atau absensi akan muncul di sini.',
                    ])
                @endforelse
            </div>
        </section>

        <section class="rounded-3xl border bg-white p-5 text-sm text-slate-500 shadow-sm">
            <h2 class="font-black text-slate-950">
                Catatan
            </h2>

            <p class="mt-2">
                Jadwal praktikum diambil dari data kelas. Agar muncul di kalender, format jadwal kelas sebaiknya berisi nama hari, misalnya:
            </p>

            <div class="mt-3 rounded-2xl bg-slate-50 p-3 font-mono text-xs text-slate-700">
                Senin, 10:00-12:00
            </div>
        </section>
    </aside>
</div>
@endsection
