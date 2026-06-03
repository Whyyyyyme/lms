@extends('layouts.app', ['title' => 'Absensi Saya'])

@section('title', 'Absensi Saya')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Mahasiswa',
    'title' => 'Absensi Saya',
    'description' => 'Lihat sesi absensi praktikum dan lakukan check-in saat absensi dibuka.'
])

@if (session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

@if (session('status'))
    <div class="alert">
        {{ session('status') }}
    </div>
@endif

@if (session('error'))
    <div class="alert alert-error">
        {{ session('error') }}
    </div>
@endif

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>Mata Kuliah / Kelas</th>
                <th>Dibuka</th>
                <th>Ditutup</th>
                <th>Status Sesi</th>
                <th>Status Kamu</th>
                <th>Check-in</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($attendances as $attendance)
                @php
                    $record = $attendance->records->first();
                    $studentStatus = $record?->status ?? 'alpha';

                    $studentStatusLabel = match ($studentStatus) {
                        'hadir' => 'Hadir',
                        'izin' => 'Izin',
                        default => 'Alpha',
                    };

                    $now = now();

                    $isScheduled = $attendance->opened_at && $attendance->opened_at->greaterThan($now);
                    $isClosed = $attendance->closed_at && $attendance->closed_at->lessThanOrEqualTo($now);

                    $sessionStatus = 'Ditutup';

                    if ($attendance->is_open) {
                        $sessionStatus = 'Dibuka';
                    } elseif ($isScheduled) {
                        $sessionStatus = 'Terjadwal';
                    } elseif ($isClosed) {
                        $sessionStatus = 'Ditutup';
                    }

                    $canCheckIn = $attendance->is_open
                        && $studentStatus === 'alpha'
                        && (! $attendance->opened_at || $attendance->opened_at->lessThanOrEqualTo($now))
                        && (! $attendance->closed_at || $attendance->closed_at->greaterThan($now));
                @endphp

                <tr>
                    <td>
                        <strong>
                            {{ $attendance->kelas?->course?->name ?? 'Mata kuliah tidak ditemukan' }}
                        </strong>

                        <div style="font-size:12px;color:#64748b;margin-top:4px;">
                            {{ $attendance->kelas?->name ?? 'Kelas tidak ditemukan' }}

                            @if($attendance->kelas?->course?->studySemester)
                                · {{ $attendance->kelas->course->studySemester->name }}
                            @endif
                        </div>
                    </td>

                    <td>
                        {{ $attendance->opened_at?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '-' }}
                        @if($attendance->opened_at)
                            WIB
                        @endif
                    </td>

                    <td>
                        {{ $attendance->closed_at?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '-' }}
                        @if($attendance->closed_at)
                            WIB
                        @endif
                    </td>

                    <td>
                        <span class="badge {{ $attendance->is_open ? 'badge-green' : '' }}">
                            {{ $sessionStatus }}
                        </span>
                    </td>

                    <td>
                        <span class="badge {{ $studentStatus === 'hadir' ? 'badge-green' : '' }}">
                            {{ $studentStatusLabel }}
                        </span>
                    </td>

                    <td>
                        {{ $record?->checked_at?->timezone('Asia/Jakarta')->format('d M Y H:i') ?? '-' }}
                        @if($record?->checked_at)
                            WIB
                        @endif
                    </td>

                    <td>
                        @if($canCheckIn)
                            <form action="{{ route('student.attendances.check-in', $attendance) }}" method="POST">
                                @csrf

                                <button type="submit" class="btn btn-primary btn-sm">
                                    Check-in
                                </button>
                            </form>
                        @elseif($studentStatus === 'hadir')
                            <span class="badge badge-green">Sudah check-in</span>
                        @elseif($studentStatus === 'izin')
                            <span class="badge">Izin</span>
                        @elseif($isScheduled)
                            <span class="badge">Belum dibuka</span>
                        @else
                            <span class="badge">Tidak tersedia</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        Belum ada sesi absensi untuk kelas praktikum kamu.
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
