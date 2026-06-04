@extends('layouts.app')

@section('title', 'Absensi')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Asisten',
    'title' => 'Absensi Praktikum',
    'description' => 'Kelola sesi absensi berdasarkan tanggal dan jam dibuka sampai tanggal dan jam ditutup.'
])

<div class="toolbar">
    <span></span>

    <a href="{{ route('assistant.attendances.create') }}" class="btn btn-primary">
        + Buat Sesi Absensi
    </a>
</div>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>Kelas / Mata Kuliah</th>
                <th>Waktu Absensi</th>
                <th>Status</th>
                <th>Records</th>
                <th>Dibuat Oleh</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($attendances as $attendance)
                @php
                    $courseName = $attendance->kelas?->course?->name ?? '-';
                    $courseCode = $attendance->kelas?->course?->code;
                    $className = $attendance->kelas?->name ?? '-';
                    $semesterName = $attendance->kelas?->course?->studySemester?->name;

                    $openedAt = $attendance->opened_at
                        ? $attendance->opened_at->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y H:i')
                        : '-';

                    $closedAt = $attendance->closed_at
                        ? $attendance->closed_at->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y H:i')
                        : '-';

                    $statusLabel = method_exists($attendance, 'statusLabel')
                        ? $attendance->statusLabel()
                        : ($attendance->is_open ? 'Sedang Dibuka' : 'Ditutup');

                    $statusClass = method_exists($attendance, 'statusBadgeClass')
                        ? $attendance->statusBadgeClass()
                        : ($attendance->is_open ? 'badge-green' : 'badge-red');
                @endphp

                <tr>
                    <td>
                        <strong>{{ $courseName }}</strong>

                        @if($courseCode)
                            <br>
                            <small>{{ $courseCode }}</small>
                        @endif

                        <br>
                        <small>
                            {{ $className }}

                            @if($semesterName)
                                · {{ $semesterName }}
                            @endif
                        </small>
                    </td>

                    <td>
                        <strong>Dibuka:</strong>
                        {{ $openedAt }} WIB

                        <br>

                        <strong>Ditutup:</strong>
                        {{ $closedAt }} WIB
                    </td>

                    <td>
                        <span class="badge {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </td>

                    <td>
                        {{ $attendance->records_count ?? $attendance->records->count() }}
                    </td>

                    <td>
                        {{ $attendance->opener?->name ?? '-' }}
                    </td>

                    <td class="actions-inline">
                        <a class="btn btn-sm" href="{{ route('assistant.attendances.show', $attendance) }}">
                            Kelola
                        </a>

                        @include('partials.delete-button', [
                            'action' => route('assistant.attendances.destroy', $attendance)
                        ])
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        Belum ada sesi absensi.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">
    {{ $attendances->links() }}
</div>
@endsection