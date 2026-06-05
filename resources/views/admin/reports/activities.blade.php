@extends('layouts.app')

@section('title', 'Laporan Aktivitas')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $statistics = $statistics ?? [];
    $notificationTypes = $notificationTypes ?? collect();
    $activities = $activities ?? collect();

    $dashboardUrl = Route::has('admin.dashboard')
        ? route('admin.dashboard')
        : '#';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Laporan Admin</div>

    <h1>Aktivitas Sistem</h1>

    <p>
        Pantau aktivitas notifikasi sistem untuk asisten dan mahasiswa berdasarkan tipe aktivitas,
        penerima, status baca, dan rentang tanggal.
    </p>

    <div class="hero-actions">
        <a href="{{ $dashboardUrl }}" class="btn">
            ← Dashboard
        </a>
    </div>
</section>

<div class="grid grid-4" style="margin-bottom: 18px;">
    <div class="stat-card">
        <div class="stat-label">Total Aktivitas</div>
        <div class="stat-value">
            {{ $statistics['total_activities'] ?? 0 }}
        </div>
        <div class="stat-note">
            Total aktivitas notifikasi yang tercatat.
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Belum Dibaca</div>
        <div class="stat-value">
            {{ $statistics['unread_activities'] ?? 0 }}
        </div>
        <div class="stat-note">
            Aktivitas yang belum dibaca penerima.
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Sudah Dibaca</div>
        <div class="stat-value">
            {{ $statistics['read_activities'] ?? 0 }}
        </div>
        <div class="stat-note">
            Aktivitas yang sudah dibaca penerima.
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Hari Ini</div>
        <div class="stat-value">
            {{ $statistics['today_activities'] ?? 0 }}
        </div>
        <div class="stat-note">
            Aktivitas yang dibuat pada hari ini.
        </div>
    </div>
</div>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Filter Aktivitas Sistem</h2>
            <div class="section-subtitle">
                Gunakan filter untuk melihat aktivitas berdasarkan judul, pesan, user, tipe, role penerima, status baca, atau rentang tanggal.
            </div>
        </div>
    </div>

    <form method="GET" class="actions-inline">
        <input
            class="form-control"
            style="width: 240px;"
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari judul/pesan/user"
        >

        <select class="form-control" style="width: 220px;" name="type">
            <option value="">Semua tipe aktivitas</option>

            @foreach($notificationTypes as $type)
                <option value="{{ $type }}" @selected(request('type') === $type)>
                    {{ str_replace(['_', '.'], ' ', $type) }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width: 170px;" name="read_status">
            <option value="">Semua status</option>

            <option value="unread" @selected(request('read_status') === 'unread')>
                Belum dibaca
            </option>

            <option value="read" @selected(request('read_status') === 'read')>
                Sudah dibaca
            </option>
        </select>

        <select class="form-control" style="width: 170px;" name="role">
            <option value="">Semua penerima</option>

            <option value="asisten" @selected(request('role') === 'asisten')>
                Asisten
            </option>

            <option value="mahasiswa" @selected(request('role') === 'mahasiswa')>
                Mahasiswa
            </option>
        </select>

        <input
            class="form-control"
            style="width: 160px;"
            type="date"
            name="date_from"
            value="{{ request('date_from') }}"
        >

        <input
            class="form-control"
            style="width: 160px;"
            type="date"
            name="date_to"
            value="{{ request('date_to') }}"
        >

        <button class="btn" type="submit">
            Filter
        </button>

        @if(request()->hasAny(['search', 'type', 'read_status', 'role', 'date_from', 'date_to']))
            <a href="{{ route('admin.reports.activities') }}" class="btn">
                Reset
            </a>
        @endif
    </form>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Data Aktivitas Sistem</h2>
            <div class="section-subtitle">
                Daftar aktivitas notifikasi beserta waktu, tipe, penerima, role, dan status baca.
            </div>
        </div>
    </div>

    @if($activities->count() === 0)
        <div class="empty-state">
            <h3 class="empty-state-title">
                Belum ada aktivitas sistem
            </h3>

            <p class="empty-state-text">
                Belum ada aktivitas sistem sesuai filter yang dipilih.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Aktivitas</th>
                            <th>Tipe</th>
                            <th>Penerima</th>
                            <th>Role</th>
                            <th>Status Baca</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($activities as $activity)
                            @php
                                $userRole = $activity->user?->roles?->pluck('name')->first() ?: $activity->user?->role;

                                $roleLabel = match ($userRole) {
                                    'asisten' => 'Asisten',
                                    'mahasiswa' => 'Mahasiswa',
                                    'admin' => 'Admin',
                                    default => '-',
                                };

                                $roleClass = match ($userRole) {
                                    'asisten' => 'status-info',
                                    'mahasiswa' => 'status-success',
                                    'admin' => 'status-warning',
                                    default => 'status-muted',
                                };

                                $typeLabel = str_replace(['_', '.'], ' ', $activity->type);
                            @endphp

                            <tr>
                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $activity->created_at?->format('d M Y') ?? '-' }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ $activity->created_at?->format('H:i') ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $activity->title ?? $activity->type }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ $activity->message ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="status-pill status-muted">
                                        {{ $typeLabel }}
                                    </span>
                                </td>

                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $activity->user?->name ?? '-' }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ $activity->user?->email ?? '-' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="status-pill {{ $roleClass }}">
                                        {{ $roleLabel }}
                                    </span>
                                </td>

                                <td>
                                    @if($activity->read_at)
                                        <div style="display: grid; gap: 5px;">
                                            <span class="status-pill status-success">
                                                Sudah dibaca
                                            </span>

                                            <span class="item-meta" style="margin-top: 0;">
                                                {{ $activity->read_at?->format('d M Y H:i') }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="status-pill status-danger">
                                            Belum dibaca
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 18px;">
            {{ $activities->links() }}
        </div>
    @endif
</section>
@endsection