@extends('layouts.app', ['title' => 'Absensi Saya'])

@section('title', 'Absensi Saya')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Mahasiswa',
    'title' => 'Absensi Saya',
    'description' => 'Lihat sesi absensi praktikum dan lakukan check-in saat absensi sedang dibuka.'
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
                <th>Waktu Absensi</th>
                <th>Status Sesi</th>
                <th>Status Kamu</th>
                <th>Check-in</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($attendances as $attendance)
                @php
                    $timezone = config('app.timezone', 'Asia/Jakarta');

                    $record = $attendance->records->first();
                    $studentStatus = $record?->status ?? 'alpha';

                    $studentStatusLabel = match ($studentStatus) {
                        'hadir' => 'Hadir',
                        'izin' => 'Izin',
                        default => 'Alpha',
                    };

                    $studentStatusClass = match ($studentStatus) {
                        'hadir' => 'badge-green',
                        'izin' => 'badge-blue',
                        default => 'badge-red',
                    };

                    $openedAt = $attendance->opened_at
                        ? $attendance->opened_at->timezone($timezone)->format('d M Y H:i') . ' WIB'
                        : '-';

                    $closedAt = $attendance->closed_at
                        ? $attendance->closed_at->timezone($timezone)->format('d M Y H:i') . ' WIB'
                        : '-';

                    $checkedAt = $record?->checked_at
                        ? $record->checked_at->timezone($timezone)->format('d M Y H:i') . ' WIB'
                        : '-';

                    $sessionStatus = method_exists($attendance, 'statusLabel')
                        ? $attendance->statusLabel()
                        : ($attendance->is_open ? 'Sedang Dibuka' : 'Ditutup');

                    $sessionStatusClass = method_exists($attendance, 'statusBadgeClass')
                        ? $attendance->statusBadgeClass()
                        : ($attendance->is_open ? 'badge-green' : 'badge-red');

                    $isScheduled = method_exists($attendance, 'isWaitingToOpen')
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

                    $canCheckIn = $isWithinOpenWindow
                        && $studentStatus === 'alpha';
                @endphp

                <tr>
                    <td>
                        <strong>
                            {{ $attendance->kelas?->course?->name ?? 'Mata kuliah tidak ditemukan' }}
                        </strong>

                        @if($attendance->kelas?->course?->code)
                            <br>
                            <small>{{ $attendance->kelas->course->code }}</small>
                        @endif

                        <div style="font-size:12px;color:#64748b;margin-top:4px;">
                            {{ $attendance->kelas?->name ?? 'Kelas tidak ditemukan' }}

                            @if($attendance->kelas?->course?->studySemester)
                                · {{ $attendance->kelas->course->studySemester->name }}
                            @endif
                        </div>
                    </td>

                    <td>
                        <strong>Dibuka:</strong>
                        {{ $openedAt }}

                        <br>

                        <strong>Ditutup:</strong>
                        {{ $closedAt }}
                    </td>

                    <td>
                        <span class="badge {{ $sessionStatusClass }}">
                            {{ $sessionStatus }}
                        </span>
                    </td>

                    <td>
                        <span class="badge {{ $studentStatusClass }}">
                            {{ $studentStatusLabel }}
                        </span>
                    </td>

                    <td>
                        {{ $checkedAt }}
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
                            <span class="badge badge-green">
                                Sudah check-in
                            </span>
                        @elseif($studentStatus === 'izin')
                            <span class="badge badge-blue">
                                Izin
                            </span>
                        @elseif($isScheduled)
                            <span class="badge badge-blue">
                                Belum dibuka
                            </span>
                        @elseif($hasEnded)
                            <span class="badge badge-red">
                                Sudah ditutup
                            </span>
                        @else
                            <span class="badge">
                                Tidak tersedia
                            </span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
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