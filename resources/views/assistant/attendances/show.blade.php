@extends('layouts.app')

@section('title', 'Kelola Absensi')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Asisten',
    'title' => 'Kelola Absensi'
])

@php
    $now = now();
    $isScheduled = $attendance->opened_at && $attendance->opened_at->greaterThan($now);
    $isClosed = $attendance->closed_at && $attendance->closed_at->lessThanOrEqualTo($now);

    $statusText = 'Ditutup';
    if ($attendance->is_open) {
        $statusText = 'Dibuka';
    } elseif ($isScheduled) {
        $statusText = 'Terjadwal';
    } elseif ($isClosed) {
        $statusText = 'Ditutup';
    }
@endphp

<div class="form-card">
    <p>
        <strong>Kelas:</strong>
        {{ $attendance->kelas?->course?->name }} - {{ $attendance->kelas?->name }}
    </p>

    <p>
        <strong>Tanggal Dibuka:</strong>
        {{ $attendance->opened_at?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '-' }}
        @if($attendance->opened_at)
            WIB
        @endif
    </p>

    <p>
        <strong>Tanggal Ditutup:</strong>
        {{ $attendance->closed_at?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '-' }}
        @if($attendance->closed_at)
            WIB
        @endif
    </p>

    <p>
        <strong>Status:</strong>
        {{ $statusText }}
    </p>

    <div class="actions-inline" style="margin:12px 0;">
        @if(! $attendance->is_open && ! $isClosed)
            <form action="{{ route('assistant.attendances.open', $attendance) }}" method="POST">
                @csrf
                @method('PATCH')
                <button class="btn btn-primary">Buka Sekarang</button>
            </form>
        @endif

        @if($attendance->is_open)
            <form action="{{ route('assistant.attendances.close', $attendance) }}" method="POST">
                @csrf
                @method('PATCH')
                <button class="btn">Tutup Sekarang</button>
            </form>
        @endif
    </div>
</div>

<div style="height:16px;"></div>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>Mahasiswa</th>
                <th>Status</th>
                <th>Check-in</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendance->records as $record)
                <tr>
                    <td>
                        {{ $record->student?->name }}
                        @if($record->student?->student_group)
                            <div style="font-size:12px;color:#64748b;">
                                Kelas {{ $record->student->student_group }}
                            </div>
                        @endif
                    </td>
                    <td>{{ ucfirst($record->status) }}</td>
                    <td>{{ $record->checked_at?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '-' }}</td>
                    <td>
                        <form
                            action="{{ route('assistant.attendances.records.update', ['attendance' => $attendance, 'record' => $record]) }}"
                            method="POST"
                            class="actions-inline"
                        >
                            @csrf
                            @method('PATCH')

                            <select class="form-control" name="status" style="width:130px;">
                                <option value="hadir" @selected($record->status === 'hadir')>Hadir</option>
                                <option value="izin" @selected($record->status === 'izin')>Izin</option>
                                <option value="alpha" @selected($record->status === 'alpha')>Alpha</option>
                            </select>

                            <button class="btn btn-sm">Simpan</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Belum ada record absensi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
