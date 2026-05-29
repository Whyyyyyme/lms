@extends('layouts.app')

@section('title', 'Dashboard Mahasiswa')
@section('page_title', 'Dashboard Mahasiswa')

@section('content')
@php
    use Illuminate\Support\Facades\Route;
    $safeRoute = fn (string $name, array $params = [], string $fallback = '#') => Route::has($name) ? route($name, $params) : $fallback;
    $statistics = $statistics ?? [];
    $latestMaterials = collect($latestMaterials ?? []);
    $upcomingAssignments = collect($upcomingAssignments ?? []);
    $announcements = collect($announcements ?? []);
@endphp

@include('partials.page-header', [
    'title' => 'Dashboard Mahasiswa',
    'description' => 'Akses materi, tugas, nilai, absensi, jadwal praktikum, pengumuman, dan AI chatbot dari satu halaman.',
])

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
    @include('partials.stat-card', ['label' => 'Materi', 'value' => $statistics['total_materi'] ?? 0, 'icon' => '📘'])
    @include('partials.stat-card', ['label' => 'Tugas', 'value' => $statistics['total_tugas'] ?? 0, 'icon' => '📝'])
    @include('partials.stat-card', ['label' => 'Belum Submit', 'value' => $statistics['tugas_belum_dikumpulkan'] ?? 0, 'icon' => '⏳'])
    @include('partials.stat-card', ['label' => 'Nilai Masuk', 'value' => $statistics['total_nilai'] ?? 0, 'icon' => '🏆'])
    @include('partials.stat-card', ['label' => 'Absensi Buka', 'value' => $statistics['absensi_terbuka'] ?? 0, 'icon' => '✅'])
</div>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @include('partials.action-card', ['title' => 'Lihat Materi', 'description' => 'Baca materi, download file, atau buka link video praktikum.', 'href' => $safeRoute('student.materials.index'), 'icon' => '📘'])
    @include('partials.action-card', ['title' => 'Kerjakan Tugas', 'description' => 'Cek deadline dan upload submission tugas praktikum.', 'href' => $safeRoute('student.assignments.index'), 'icon' => '📝'])
    @include('partials.action-card', ['title' => 'Lihat Nilai', 'description' => 'Pantau skor dan feedback dari asisten praktikum.', 'href' => $safeRoute('student.grades.index'), 'icon' => '🏆'])
    @include('partials.action-card', ['title' => 'Absensi Mandiri', 'description' => 'Check-in ketika asisten membuka sesi absensi.', 'href' => $safeRoute('student.attendances.index'), 'icon' => '✅'])
    @include('partials.action-card', ['title' => 'Jadwal Praktikum', 'description' => 'Lihat kelas, ruangan, dan jadwal akademik praktikum.', 'href' => $safeRoute('student.schedule.index'), 'icon' => '🗓️'])
    @include('partials.action-card', ['title' => 'AI Chatbot', 'description' => 'Tanya materi, ringkas penjelasan, dan cek tugas yang belum dikumpulkan.', 'href' => $safeRoute('student.chatbot.index'), 'icon' => '🤖'])
</div>

<div class="grid gap-6 xl:grid-cols-3">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-1">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-black text-slate-950">Materi Terbaru</h3>
            <a href="{{ $safeRoute('student.materials.index') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">Semua</a>
        </div>
        @if($latestMaterials->isEmpty())
            @include('partials.empty-state', ['title' => 'Belum ada materi', 'description' => 'Materi dari asisten akan tampil di sini.', 'icon' => '📘'])
        @else
            <div class="space-y-3">
                @foreach($latestMaterials as $material)
                    <a href="{{ $safeRoute('student.materials.show', ['material' => $material->id]) }}" class="block rounded-2xl border border-slate-200 bg-slate-50 p-4 hover:bg-indigo-50">
                        <p class="font-bold text-slate-950">{{ $material->title }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ $material->kelas->course->name ?? 'Matakuliah' }}</p>
                        <p class="mt-2 text-xs text-slate-400">{{ optional($material->published_at)->translatedFormat('d M Y') ?? '-' }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-1">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-black text-slate-950">Deadline Tugas</h3>
            <a href="{{ $safeRoute('student.assignments.index') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">Semua</a>
        </div>
        @if($upcomingAssignments->isEmpty())
            @include('partials.empty-state', ['title' => 'Tidak ada deadline', 'description' => 'Tugas aktif akan tampil di sini.', 'icon' => '📝'])
        @else
            <div class="space-y-3">
                @foreach($upcomingAssignments as $assignment)
                    @php
                        $submitted = $assignment->relationLoaded('submissions') ? $assignment->submissions->isNotEmpty() : false;
                    @endphp
                    <a href="{{ $safeRoute('student.assignments.show', ['assignment' => $assignment->id]) }}" class="block rounded-2xl border border-slate-200 bg-slate-50 p-4 hover:bg-indigo-50">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-bold text-slate-950">{{ $assignment->title }}</p>
                                <p class="mt-1 text-sm text-slate-500">{{ $assignment->kelas->course->name ?? 'Matakuliah' }}</p>
                            </div>
                            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $submitted ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700' }}">
                                {{ $submitted ? 'Sudah' : 'Belum' }}
                            </span>
                        </div>
                        <p class="mt-2 text-xs font-semibold text-slate-400">Deadline: {{ optional($assignment->deadline)->translatedFormat('d M Y H:i') ?? '-' }}</p>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm xl:col-span-1">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-black text-slate-950">Pengumuman</h3>
        </div>
        @if($announcements->isEmpty())
            @include('partials.empty-state', ['title' => 'Belum ada pengumuman', 'description' => 'Pengumuman dari asisten akan tampil di sini.', 'icon' => '📢'])
        @else
            <div class="space-y-3">
                @foreach($announcements as $announcement)
                    <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                        <p class="font-bold text-slate-950">{{ $announcement->title }}</p>
                        <p class="mt-1 line-clamp-3 text-sm leading-6 text-slate-500">{{ $announcement->content }}</p>
                        <p class="mt-2 text-xs text-slate-400">{{ optional($announcement->created_at)->translatedFormat('d M Y H:i') ?? '-' }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
