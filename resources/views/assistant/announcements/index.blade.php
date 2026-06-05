@extends('layouts.app')

@section('title', 'Pengumuman')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $timezone = config('app.timezone', 'Asia/Jakarta');

    $dashboardUrl = Route::has('assistant.dashboard')
        ? route('assistant.dashboard')
        : '#';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Asisten Praktikum</div>

    <h1>Pengumuman</h1>

    <p>
        Kelola pengumuman untuk mahasiswa kelas praktikum.
        Pengumuman dapat digunakan untuk menyampaikan informasi penting terkait materi, tugas, absensi, atau jadwal praktikum.
    </p>

    <div class="hero-actions">
        <a href="{{ $dashboardUrl }}" class="btn">
            ← Dashboard
        </a>

        <a href="{{ route('assistant.pengumuman.create') }}" class="btn btn-primary">
            + Buat Pengumuman
        </a>
    </div>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Pengumuman</h2>
            <div class="section-subtitle">
                Pengumuman ditampilkan berdasarkan kelas praktikum yang kamu kelola.
            </div>
        </div>

        <a href="{{ route('assistant.pengumuman.create') }}" class="btn btn-primary btn-sm">
            + Buat Pengumuman
        </a>
    </div>

    @if($announcements->count() === 0)
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">📢</div>

            <h3 class="empty-state-title">
                Belum ada pengumuman
            </h3>

            <p class="empty-state-text">
                Pengumuman yang kamu buat akan tampil di halaman ini.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Judul</th>
                            <th>Kelas</th>
                            <th>Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($announcements as $announcement)
                            <tr>
                                <td>
                                    <div style="display: grid; gap: 6px;">
                                        <strong>
                                            {{ $announcement->title }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ str($announcement->content)->limit(110) }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <div style="display: grid; gap: 6px;">
                                        <strong>
                                            {{ $announcement->kelas?->course?->name ?? 'Mata kuliah tidak ditemukan' }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ $announcement->kelas?->name ?? 'Kelas tidak ditemukan' }}

                                            @if($announcement->kelas?->course?->studySemester)
                                                · {{ $announcement->kelas->course->studySemester->name }}
                                            @endif
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $announcement->created_at ? $announcement->created_at->timezone($timezone)->format('d M Y H:i').' WIB' : '-' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="actions-inline">
                                        <a
                                            class="btn btn-sm"
                                            href="{{ route('assistant.pengumuman.show', $announcement) }}"
                                        >
                                            Detail
                                        </a>

                                        <a
                                            class="btn btn-sm"
                                            href="{{ route('assistant.pengumuman.edit', $announcement) }}"
                                        >
                                            Edit
                                        </a>

                                        @include('partials.delete-button', [
                                            'action' => route('assistant.pengumuman.destroy', $announcement)
                                        ])
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 18px;">
            {{ $announcements->links() }}
        </div>
    @endif
</section>
@endsection