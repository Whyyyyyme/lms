@extends('layouts.app')

@section('title', 'Kelola Absensi')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Asisten',
    'title' => 'Kelola Absensi',
    'description' => 'Kelola status kehadiran mahasiswa berdasarkan sesi absensi yang sudah dijadwalkan.'
])

@php
    $timezone = config('app.timezone', 'Asia/Jakarta');

    $courseName = $attendance->kelas?->course?->name ?? '-';
    $courseCode = $attendance->kelas?->course?->code;
    $className = $attendance->kelas?->name ?? '-';
    $semesterName = $attendance->kelas?->course?->studySemester?->name;

    $openedAt = $attendance->opened_at
        ? $attendance->opened_at->timezone($timezone)->format('d M Y H:i')
        : '-';

    $closedAt = $attendance->closed_at
        ? $attendance->closed_at->timezone($timezone)->format('d M Y H:i')
        : '-';

    $statusText = method_exists($attendance, 'statusLabel')
        ? $attendance->statusLabel()
        : ($attendance->is_open ? 'Sedang Dibuka' : 'Ditutup');

    $statusClass = method_exists($attendance, 'statusBadgeClass')
        ? $attendance->statusBadgeClass()
        : ($attendance->is_open ? 'badge-green' : 'badge-red');

    $isWaitingToOpen = method_exists($attendance, 'isWaitingToOpen')
        ? $attendance->isWaitingToOpen()
        : ($attendance->opened_at && $attendance->opened_at->greaterThan(now()));

    $isWithinOpenWindow = method_exists($attendance, 'isWithinOpenWindow')
        ? $attendance->isWithinOpenWindow()
        : (
            $attendance->opened_at
            && $attendance->closed_at
            && $attendance->opened_at->lessThanOrEqualTo(now())
            && $attendance->closed_at->greaterThan(now())
        );

    $hasEnded = method_exists($attendance, 'hasEnded')
        ? $attendance->hasEnded()
        : ($attendance->closed_at && $attendance->closed_at->lessThanOrEqualTo(now()));

    $totalRecords = $attendance->records->count();
    $totalHadir = $attendance->records->where('status', 'hadir')->count();
    $totalIzin = $attendance->records->where('status', 'izin')->count();
    $totalAlpha = $attendance->records->where('status', 'alpha')->count();
@endphp

<div class="form-card">
    <div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start;flex-wrap:wrap;">
        <div>
            <p style="margin-bottom:8px;">
                <strong>Mata Kuliah:</strong>
                {{ $courseName }}

                @if($courseCode)
                    <small>({{ $courseCode }})</small>
                @endif
            </p>

            <p style="margin-bottom:8px;">
                <strong>Kelas:</strong>
                {{ $className }}

                @if($semesterName)
                    <small>· {{ $semesterName }}</small>
                @endif
            </p>

            <p style="margin-bottom:8px;">
                <strong>Dibuka:</strong>
                {{ $openedAt }} WIB
            </p>

            <p style="margin-bottom:8px;">
                <strong>Ditutup:</strong>
                {{ $closedAt }} WIB
            </p>
        </div>

        <div>
            <span class="badge {{ $statusClass }}">
                {{ $statusText }}
            </span>
        </div>
    </div>

    <div style="height:12px;"></div>

    <div class="actions-inline">
        <a href="{{ route('assistant.attendances.index') }}" class="btn">
            Kembali
        </a>

        @if($isWaitingToOpen)
            <span class="badge badge-blue">
                Absensi akan otomatis dibuka sesuai jadwal
            </span>
        @endif

        @if($isWithinOpenWindow && ! $attendance->is_open)
            <form action="{{ route('assistant.attendances.open', $attendance) }}" method="POST">
                @csrf
                @method('PATCH')

                <button class="btn btn-primary" type="submit">
                    Aktifkan Dibuka
                </button>
            </form>
        @endif

        @if($isWithinOpenWindow && $attendance->is_open)
            <form
                action="{{ route('assistant.attendances.close', $attendance) }}"
                method="POST"
                onsubmit="return confirm('Tutup absensi lebih awal? Mahasiswa tidak bisa check-in lagi setelah sesi ditutup.')"
            >
                @csrf
                @method('PATCH')

                <button class="btn" type="submit">
                    Tutup Lebih Awal
                </button>
            </form>
        @endif

        @if($hasEnded)
            <span class="badge badge-red">
                Absensi sudah ditutup
            </span>
        @endif
    </div>
</div>

<div style="height:16px;"></div>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>Total Mahasiswa</th>
                <th>Hadir</th>
                <th>Izin</th>
                <th>Alpha</th>
            </tr>
        </thead>

        <tbody>
            <tr>
                <td>{{ $totalRecords }}</td>
                <td>
                    <span class="badge badge-green">
                        {{ $totalHadir }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-blue">
                        {{ $totalIzin }}
                    </span>
                </td>
                <td>
                    <span class="badge badge-red">
                        {{ $totalAlpha }}
                    </span>
                </td>
            </tr>
        </tbody>
    </table>
</div>

<div style="height:16px;"></div>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>Mahasiswa</th>
                <th>Rombel</th>
                <th>Status</th>
                <th>Check-in</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($attendance->records as $record)
                @php
                    $recordStatusClass = match ($record->status) {
                        'hadir' => 'badge-green',
                        'izin' => 'badge-blue',
                        default => 'badge-red',
                    };

                    $recordStatusLabel = match ($record->status) {
                        'hadir' => 'Hadir',
                        'izin' => 'Izin',
                        default => 'Alpha',
                    };

                    $checkedAt = $record->checked_at
                        ? $record->checked_at->timezone($timezone)->format('d M Y H:i') . ' WIB'
                        : '-';
                @endphp

                <tr>
                    <td>
                        <strong>{{ $record->student?->name ?? '-' }}</strong>

                        @if($record->student?->nim_nip)
                            <br>
                            <small>{{ $record->student->nim_nip }}</small>
                        @endif
                    </td>

                    <td>
                        {{ $record->student?->student_group ? 'Kelas '.$record->student->student_group : '-' }}
                    </td>

                    <td>
                        <span class="badge {{ $recordStatusClass }}">
                            {{ $recordStatusLabel }}
                        </span>
                    </td>

                    <td>
                        {{ $checkedAt }}
                    </td>

                    <td>
                        <form
                            action="{{ route('assistant.attendances.records.update', ['attendance' => $attendance, 'record' => $record]) }}"
                            method="POST"
                            class="actions-inline"
                        >
                            @csrf
                            @method('PATCH')

                            <select class="form-control" name="status" style="width:130px;">
                                <option value="hadir" @selected($record->status === 'hadir')>
                                    Hadir
                                </option>
                                <option value="izin" @selected($record->status === 'izin')>
                                    Izin
                                </option>
                                <option value="alpha" @selected($record->status === 'alpha')>
                                    Alpha
                                </option>
                            </select>

                            <button class="btn btn-sm" type="submit">
                                Simpan
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        Belum ada record absensi.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection