@extends('layouts.app')

@section('title', 'Absensi Saya')

@section('content')
<section class="dashboard-hero">
    <div class="eyebrow">Mahasiswa</div>

    <h1>Absensi Saya</h1>

    <p>
        Lihat sesi absensi praktikum dari kelas yang kamu ikuti.
        Lakukan check-in saat sesi absensi sedang dibuka sesuai waktu yang ditentukan.
    </p>

    <div class="hero-actions">
        @if(\Illuminate\Support\Facades\Route::has('student.dashboard'))
            <a href="{{ route('student.dashboard') }}" class="btn">
                ← Dashboard
            </a>
        @endif

        @if(\Illuminate\Support\Facades\Route::has('student.courses.index'))
            <a href="{{ route('student.courses.index') }}" class="btn btn-primary">
                📚 Mata Kuliah Saya
            </a>
        @endif
    </div>
</section>

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

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Sesi Absensi</h2>
            <div class="section-subtitle">
                Absensi ditampilkan berdasarkan kelas praktikum yang dapat kamu akses.
            </div>
        </div>
    </div>

    @if($attendances->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">✅</div>

            <h3 class="empty-state-title">
                Belum ada sesi absensi
            </h3>

            <p class="empty-state-text">
                Sesi absensi dari asisten akan tampil di halaman ini jika sudah dibuat.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
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
                        @foreach($attendances as $attendance)
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
                                    'hadir' => 'status-success',
                                    'izin' => 'status-info',
                                    default => 'status-danger',
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

                                $rawSessionStatusClass = method_exists($attendance, 'statusBadgeClass')
                                    ? $attendance->statusBadgeClass()
                                    : ($attendance->is_open ? 'badge-green' : 'badge-red');

                                $sessionStatusClass = match ($rawSessionStatusClass) {
                                    'badge-green' => 'status-success',
                                    'badge-blue' => 'status-info',
                                    'badge-red' => 'status-danger',
                                    'badge-yellow' => 'status-warning',
                                    default => 'status-muted',
                                };

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

                                $canCheckIn = $isWithinOpenWindow && $studentStatus === 'alpha';
                            @endphp

                            <tr>
                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $attendance->kelas?->course?->name ?? 'Mata kuliah tidak ditemukan' }}
                                        </strong>

                                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            @if($attendance->kelas?->course?->code)
                                                <span class="course-code">
                                                    {{ $attendance->kelas->course->code }}
                                                </span>
                                            @endif

                                            <span class="status-pill status-muted">
                                                {{ $attendance->kelas?->name ?? 'Kelas tidak ditemukan' }}
                                            </span>
                                        </div>

                                        @if($attendance->kelas?->course?->studySemester)
                                            <div class="item-meta" style="margin-top: 0;">
                                                Semester {{ $attendance->kelas->course->studySemester->name }}
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <div class="item-meta" style="margin-top: 0;">
                                        <strong>Dibuka:</strong>
                                        {{ $openedAt }}
                                        <br>
                                        <strong>Ditutup:</strong>
                                        {{ $closedAt }}
                                    </div>
                                </td>

                                <td>
                                    <span class="status-pill {{ $sessionStatusClass }}">
                                        {{ $sessionStatus }}
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill {{ $studentStatusClass }}">
                                        {{ $studentStatusLabel }}
                                    </span>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $checkedAt }}
                                    </span>
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
                                        <span class="status-pill status-success">
                                            Sudah check-in
                                        </span>
                                    @elseif($studentStatus === 'izin')
                                        <span class="status-pill status-info">
                                            Izin
                                        </span>
                                    @elseif($isScheduled)
                                        <span class="status-pill status-info">
                                            Belum dibuka
                                        </span>
                                    @elseif($hasEnded)
                                        <span class="status-pill status-danger">
                                            Sudah ditutup
                                        </span>
                                    @else
                                        <span class="status-pill status-muted">
                                            Tidak tersedia
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
            {{ $attendances->links() }}
        </div>
    @endif
</section>
@endsection