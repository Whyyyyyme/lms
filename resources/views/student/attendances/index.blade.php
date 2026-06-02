@extends('layouts.app', ['title' => 'Absensi Saya'])

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Mahasiswa',
    'title' => 'Absensi Saya',
    'description' => 'Lihat sesi absensi praktikum dan lakukan check-in saat absensi dibuka.'
])

@if (session('success'))
    <div class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
        {{ session('success') }}
    </div>
@endif

@if (session('status'))
    <div class="mb-4 rounded-2xl border border-blue-200 bg-blue-50 px-4 py-3 text-sm text-blue-700">
        {{ session('status') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-4 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        {{ session('error') }}
    </div>
@endif

<div class="space-y-4">
    @forelse($attendances as $attendance)
        @php
            $record = $attendance->records->first();
            $status = $record?->status ?? 'alpha';

            $statusLabel = match ($status) {
                'hadir' => 'Hadir',
                'izin' => 'Izin',
                default => 'Alpha',
            };

            $statusClass = match ($status) {
                'hadir' => 'bg-emerald-100 text-emerald-700',
                'izin' => 'bg-amber-100 text-amber-700',
                default => 'bg-red-100 text-red-700',
            };

            $classType = $attendance->kelas?->class_type ?? 'regular';

            $groupText = '-';

            if ($classType === 'regular') {
                $groupText = $attendance->kelas?->student_group
                    ? 'Kelas ' . $attendance->kelas->student_group
                    : '-';
            }

            if ($classType === 'combined') {
                $members = collect($attendance->kelas?->group_members ?? [])
                    ->filter()
                    ->map(fn ($group) => 'Kelas ' . $group)
                    ->implode(', ');

                $groupText = $attendance->kelas?->group_label
                    ? $attendance->kelas->group_label . ' (' . ($members ?: '-') . ')'
                    : ($members ?: '-');
            }

            $canCheckIn = $attendance->is_open && $status === 'alpha';
        @endphp

        <article class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex flex-col justify-between gap-4 md:flex-row md:items-start">
                <div class="space-y-3">
                    <div>
                        <h2 class="text-lg font-bold text-slate-900">
                            {{ $attendance->kelas?->course?->name ?? 'Mata kuliah tidak ditemukan' }}
                            -
                            {{ $attendance->kelas?->name ?? 'Kelas tidak ditemukan' }}
                        </h2>

                        <p class="text-sm text-slate-500">
                            {{ $attendance->kelas?->course?->studySemester?->name ?? '-' }}
                            •
                            {{ $groupText }}
                        </p>
                    </div>

                    <div class="grid gap-2 text-sm text-slate-600 sm:grid-cols-2">
                        <p>
                            <span class="font-semibold text-slate-800">Tanggal:</span>
                            {{ $attendance->session_date?->format('d M Y') ?? '-' }}
                        </p>

                        <p>
                            <span class="font-semibold text-slate-800">Status Sesi:</span>
                            {{ $attendance->is_open ? 'Dibuka' : 'Ditutup' }}
                        </p>

                        <p>
                            <span class="font-semibold text-slate-800">Check-in:</span>
                            {{ $record?->checked_at?->format('d M Y H:i') ?? '-' }}
                        </p>

                        <p>
                            <span class="font-semibold text-slate-800">Status Kamu:</span>
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">
                                {{ $statusLabel }}
                            </span>
                        </p>
                    </div>
                </div>

                <div class="flex flex-col items-start gap-2 md:items-end">
                    @if($canCheckIn)
                        <form action="{{ route('student.attendances.check-in', $attendance) }}" method="POST">
                            @csrf

                            <button
                                type="submit"
                                class="rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-emerald-700"
                            >
                                Check-in
                            </button>
                        </form>
                    @elseif($attendance->is_open && $status === 'hadir')
                        <span class="rounded-2xl bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700">
                            Sudah check-in
                        </span>
                    @elseif($attendance->is_open && $status === 'izin')
                        <span class="rounded-2xl bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-700">
                            Sudah izin
                        </span>
                    @else
                        <span class="rounded-2xl bg-slate-100 px-4 py-2 text-sm font-semibold text-slate-500">
                            Absensi ditutup
                        </span>
                    @endif
                </div>
            </div>
        </article>
    @empty
        @include('partials.empty-state', [
            'title' => 'Belum ada absensi',
            'description' => 'Sesi absensi akan muncul di sini jika asisten sudah membuat absensi untuk kelas praktikum kamu.'
        ])
    @endforelse
</div>

<div class="mt-5">
    {{ $attendances->links() }}
</div>
@endsection