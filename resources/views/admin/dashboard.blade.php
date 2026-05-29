@extends('layouts.app')

@section('title', 'Dashboard Admin')
@section('page_title', 'Dashboard Admin')

@section('content')
@php
    use Illuminate\Support\Facades\Route;
    $safeRoute = fn (string $name, array $params = [], string $fallback = '#') => Route::has($name) ? route($name, $params) : $fallback;
    $statistics = $statistics ?? [];
    $latestUsers = collect($latestUsers ?? []);
    $latestSubmissions = collect($latestSubmissions ?? []);
@endphp

@include('partials.page-header', [
    'title' => 'Dashboard Admin',
    'description' => 'Pusat kontrol untuk mengelola user, akademik, kelas praktikum, laporan nilai, absensi, dan konfigurasi sistem.',
])

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @include('partials.stat-card', ['label' => 'Total User', 'value' => $statistics['total_user'] ?? 0, 'icon' => '👥'])
    @include('partials.stat-card', ['label' => 'Mahasiswa', 'value' => $statistics['total_mahasiswa'] ?? 0, 'icon' => '🎓'])
    @include('partials.stat-card', ['label' => 'Matakuliah', 'value' => $statistics['total_matakuliah'] ?? 0, 'icon' => '📚'])
    @include('partials.stat-card', ['label' => 'Kelas Praktikum', 'value' => $statistics['total_kelas'] ?? 0, 'icon' => '🏫'])
</div>

<div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
    @include('partials.action-card', ['title' => 'Kelola User', 'description' => 'Tambah, edit, nonaktifkan admin, asisten, dan mahasiswa.', 'href' => $safeRoute('admin.users.index'), 'icon' => '👥'])
    @include('partials.action-card', ['title' => 'Tahun Akademik', 'description' => 'Atur tahun akademik aktif, semester ganjil/genap.', 'href' => $safeRoute('admin.tahun-akademik.index'), 'icon' => '📅'])
    @include('partials.action-card', ['title' => 'Matakuliah', 'description' => 'Kelola kode, SKS, status aktif, dan relasi tahun akademik.', 'href' => $safeRoute('admin.matakuliah.index'), 'icon' => '📚'])
    @include('partials.action-card', ['title' => 'Kelas Praktikum', 'description' => 'Atur kelas, asisten, ruang, jadwal, dan mahasiswa.', 'href' => $safeRoute('admin.kelas.index'), 'icon' => '🏫'])
    @include('partials.action-card', ['title' => 'Laporan Nilai', 'description' => 'Pantau rekap nilai dan export data laporan.', 'href' => $safeRoute('admin.reports.scores'), 'icon' => '🧾'])
    @include('partials.action-card', ['title' => 'Laporan Absensi', 'description' => 'Pantau kehadiran mahasiswa di seluruh kelas.', 'href' => $safeRoute('admin.reports.attendances'), 'icon' => '✅'])
    @include('partials.action-card', ['title' => 'Aktivitas Sistem', 'description' => 'Lihat aktivitas materi, tugas, submission, dan notifikasi.', 'href' => $safeRoute('admin.reports.activities'), 'icon' => '📌'])
    @include('partials.action-card', ['title' => 'Pengaturan Sistem', 'description' => 'Atur nama kampus, logo, dan konfigurasi aplikasi.', 'href' => $safeRoute('admin.settings.edit'), 'icon' => '⚙️'])
    @include('partials.action-card', ['title' => 'Panel Filament', 'description' => 'Buka admin panel Filament jika sudah aktif.', 'href' => url('/admin'), 'icon' => '🛠️'])
</div>

<div class="grid gap-6 xl:grid-cols-2">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-black text-slate-950">User Terbaru</h3>
            <a href="{{ $safeRoute('admin.users.index') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">Lihat semua</a>
        </div>

        @if($latestUsers->isEmpty())
            @include('partials.empty-state', ['title' => 'Belum ada user', 'description' => 'Data user terbaru akan tampil di sini.', 'icon' => '👥'])
        @else
            <div class="divide-y divide-slate-100">
                @foreach($latestUsers as $latestUser)
                    <div class="flex items-center justify-between gap-4 py-3">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $latestUser->name }}</p>
                            <p class="text-sm text-slate-500">{{ $latestUser->email }}</p>
                        </div>
                        <span class="inline-flex rounded-full border px-2.5 py-1 text-xs font-bold {{ $latestUser->is_active ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-rose-200 bg-rose-50 text-rose-700' }}">
                            {{ $latestUser->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-lg font-black text-slate-950">Submission Terbaru</h3>
            <a href="{{ $safeRoute('admin.reports.scores') }}" class="text-sm font-bold text-indigo-600 hover:text-indigo-700">Lihat laporan</a>
        </div>

        @if($latestSubmissions->isEmpty())
            @include('partials.empty-state', ['title' => 'Belum ada submission', 'description' => 'Submission mahasiswa akan tampil di sini.', 'icon' => '📥'])
        @else
            <div class="divide-y divide-slate-100">
                @foreach($latestSubmissions as $submission)
                    <div class="py-3">
                        <p class="font-semibold text-slate-900">{{ $submission->student->name ?? 'Mahasiswa' }}</p>
                        <p class="text-sm text-slate-500">{{ $submission->assignment->title ?? 'Tugas' }} · {{ $submission->assignment->kelas->course->name ?? 'Matakuliah' }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ optional($submission->submitted_at)->translatedFormat('d M Y H:i') ?? '-' }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
@endsection
