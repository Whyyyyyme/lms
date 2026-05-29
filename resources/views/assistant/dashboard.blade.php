@extends('layouts.app')

@section('title', 'Dashboard Asisten')
@section('page_title', 'Dashboard Asisten')

@section('content')
@php
    use Illuminate\Support\Facades\Route;
    $safeRoute = fn (string $name, array $params = [], string $fallback = '#') => Route::has($name) ? route($name, $params) : $fallback;
    $statistics = $statistics ?? [];
    $classes = collect($classes ?? []);
    $latestSubmissions = collect($latestSubmissions ?? []);
@endphp

@include('partials.page-header', [
    'title' => 'Dashboard Asisten Praktikum',
    'description' => 'Kelola materi, tugas, absensi, submission, nilai, pengumuman, dan export rekap untuk kelas yang kamu asisteni.',
])

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @include('partials.stat-card', ['label' => 'Kelas Diampu', 'value' => $statistics['total_kelas'] ?? 0, 'icon' => '🏫'])
    @include('partials.stat-card', ['label' => 'Mahasiswa', 'value' => $statistics['total_mahasiswa'] ?? 0, 'icon' => '🎓'])
    @include('partials.stat-card', ['label' => 'Materi', 'value' => $statistics['total_materi'] ?? 0, 'icon' => '📘'])
    @include('partials.stat-card', ['label' => 'Belum Dinilai', 'value' => $statistics['total_submission_belum_dinilai'] ?? 0, 'icon' => '📥'])
</div>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @include('partials.action-card', ['title' => 'Upload Materi', 'description' => 'Tambahkan PDF, dokumen, atau link video untuk mahasiswa.', 'href' => $safeRoute('assistant.materi.index'), 'icon' => '📘'])
    @include('partials.action-card', ['title' => 'Buat Tugas', 'description' => 'Buat tugas, upload lampiran, dan atur deadline.', 'href' => $safeRoute('assistant.tugas.index'), 'icon' => '📝'])
    @include('partials.action-card', ['title' => 'Submission Mahasiswa', 'description' => 'Lihat hasil upload mahasiswa dan beri feedback.', 'href' => $safeRoute('assistant.submissions.index'), 'icon' => '📥'])
    @include('partials.action-card', ['title' => 'Absensi Praktikum', 'description' => 'Buka sesi absensi dan ubah status hadir/izin/alpha.', 'href' => $safeRoute('assistant.attendances.index'), 'icon' => '✅'])
    @include('partials.action-card', ['title' => 'Pengumuman', 'description' => 'Kirim informasi ke mahasiswa di kelas tertentu.', 'href' => $safeRoute('assistant.pengumuman.index'), 'icon' => '📢'])
    @include('partials.action-card', ['title' => 'Export Rekap', 'description' => 'Download rekap nilai dan absensi untuk laporan praktikum.', 'href' => $safeRoute('assistant.exports.scores.excel'), 'icon' => '📗'])
</div>

<div class="grid gap-6 xl:grid-cols-2">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-black text-slate-950">Kelas yang Kamu Ampu</h3>
            <a href="{{ $safeRoute('assistant.attendances.index') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">Kelola absensi</a>
        </div>

        @if($classes->isEmpty())
            @include('partials.empty-state', ['title' => 'Belum ada kelas', 'description' => 'Admin perlu menugaskan kamu ke kelas praktikum.', 'icon' => '🏫'])
        @else
            <div class="grid gap-3">
                @foreach($classes as $class)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold text-slate-950">{{ $class->name }}</p>
                                <p class="text-sm text-slate-500">{{ $class->course->name ?? 'Matakuliah' }} · {{ $class->room ?? 'Ruang belum diatur' }}</p>
                            </div>
                            <span class="rounded-full bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700">{{ $class->students_count ?? $class->students->count() ?? 0 }} mhs</span>
                        </div>
                        <div class="mt-3 grid grid-cols-3 gap-2 text-center text-xs text-slate-500">
                            <div class="rounded-xl bg-white p-2"><b class="block text-slate-950">{{ $class->materials_count ?? 0 }}</b>Materi</div>
                            <div class="rounded-xl bg-white p-2"><b class="block text-slate-950">{{ $class->assignments_count ?? 0 }}</b>Tugas</div>
                            <div class="rounded-xl bg-white p-2"><b class="block text-slate-950">{{ $class->attendances_count ?? 0 }}</b>Absensi</div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-black text-slate-950">Submission Terbaru</h3>
            <a href="{{ $safeRoute('assistant.submissions.index') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">Lihat semua</a>
        </div>

        @if($latestSubmissions->isEmpty())
            @include('partials.empty-state', ['title' => 'Belum ada submission', 'description' => 'Submission mahasiswa akan muncul setelah mereka mengumpulkan tugas.', 'icon' => '📥'])
        @else
            <div class="divide-y divide-slate-100">
                @foreach($latestSubmissions as $submission)
                    <div class="flex items-center justify-between gap-4 py-3">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $submission->student->name ?? 'Mahasiswa' }}</p>
                            <p class="text-sm text-slate-500">{{ $submission->assignment->title ?? 'Tugas' }}</p>
                        </div>
                        <a href="{{ $safeRoute('assistant.submissions.show', ['submission' => $submission->id]) }}" class="rounded-xl bg-indigo-50 px-3 py-2 text-xs font-bold text-indigo-700 hover:bg-indigo-100">Nilai</a>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
