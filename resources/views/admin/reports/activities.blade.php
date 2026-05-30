@extends('layouts.app')

@section('title', 'Laporan Aktivitas')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Laporan',
    'title' => 'Aktivitas Sistem',
    'description' => 'Pantau aktivitas notifikasi sistem untuk asisten dan mahasiswa.'
])

<div class="grid grid-4" style="margin-bottom:18px;">
    @include('partials.stat-card', [
        'label' => 'Total Aktivitas',
        'value' => $statistics['total_activities'] ?? 0,
        'icon' => '📌'
    ])

    @include('partials.stat-card', [
        'label' => 'Belum Dibaca',
        'value' => $statistics['unread_activities'] ?? 0,
        'icon' => '🔔'
    ])

    @include('partials.stat-card', [
        'label' => 'Sudah Dibaca',
        'value' => $statistics['read_activities'] ?? 0,
        'icon' => '✅'
    ])

    @include('partials.stat-card', [
        'label' => 'Hari Ini',
        'value' => $statistics['today_activities'] ?? 0,
        'icon' => '📅'
    ])
</div>

<div class="toolbar">
    <form method="GET" class="actions-inline">
        <input
            class="form-control"
            style="width:240px;"
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari judul/pesan/user"
        >

        <select class="form-control" style="width:220px;" name="type">
            <option value="">Semua tipe aktivitas</option>
            @foreach($notificationTypes as $type)
                <option value="{{ $type }}" @selected(request('type') === $type)>
                    {{ str_replace(['_', '.'], ' ', $type) }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width:170px;" name="read_status">
            <option value="">Semua status</option>
            <option value="unread" @selected(request('read_status') === 'unread')>
                Belum dibaca
            </option>
            <option value="read" @selected(request('read_status') === 'read')>
                Sudah dibaca
            </option>
        </select>

        <select class="form-control" style="width:170px;" name="role">
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
            style="width:160px;"
            type="date"
            name="date_from"
            value="{{ request('date_from') }}"
        >

        <input
            class="form-control"
            style="width:160px;"
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
</div>

<div class="table-card">
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
            @forelse($activities as $activity)
                @php
                    $userRole = $activity->user?->roles?->pluck('name')->first() ?: $activity->user?->role;

                    $roleLabel = match ($userRole) {
                        'asisten' => 'Asisten',
                        'mahasiswa' => 'Mahasiswa',
                        'admin' => 'Admin',
                        default => '-',
                    };
                @endphp

                <tr>
                    <td>
                        <strong>{{ $activity->created_at?->format('d M Y') ?? '-' }}</strong>
                        <br>
                        <small>{{ $activity->created_at?->format('H:i') ?? '-' }}</small>
                    </td>

                    <td>
                        <strong>{{ $activity->title ?? $activity->type }}</strong>
                        <br>
                        <small>{{ $activity->message ?? '-' }}</small>
                    </td>

                    <td>
                        <span class="badge badge-blue">
                            {{ str_replace(['_', '.'], ' ', $activity->type) }}
                        </span>
                    </td>

                    <td>
                        <strong>{{ $activity->user?->name ?? '-' }}</strong>
                        <br>
                        <small>{{ $activity->user?->email ?? '-' }}</small>
                    </td>

                    <td>
                        {{ $roleLabel }}
                    </td>

                    <td>
                        @if($activity->read_at)
                            <span class="badge badge-green">
                                Sudah dibaca
                            </span>
                            <br>
                            <small>{{ $activity->read_at?->format('d M Y H:i') }}</small>
                        @else
                            <span class="badge badge-red">
                                Belum dibaca
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        Belum ada aktivitas sistem sesuai filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">
    {{ $activities->links() }}
</div>
@endsection