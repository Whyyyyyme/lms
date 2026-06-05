@extends('layouts.app')

@section('title', 'Kelola Absensi')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $timezone = config('app.timezone', 'Asia/Jakarta');

    $courseName = $attendance->kelas?->course?->name ?? 'Mata kuliah tidak ditemukan';
    $courseCode = $attendance->kelas?->course?->code;
    $className = $attendance->kelas?->name ?? 'Kelas tidak ditemukan';
    $semesterName = $attendance->kelas?->course?->studySemester?->name;

    $openedAt = $attendance->opened_at
        ? $attendance->opened_at->timezone($timezone)->format('d M Y H:i') . ' WIB'
        : '-';

    $closedAt = $attendance->closed_at
        ? $attendance->closed_at->timezone($timezone)->format('d M Y H:i') . ' WIB'
        : '-';

    $statusText = method_exists($attendance, 'statusLabel')
        ? $attendance->statusLabel()
        : ($attendance->is_open ? 'Sedang Dibuka' : 'Ditutup');

    $rawStatusClass = method_exists($attendance, 'statusBadgeClass')
        ? $attendance->statusBadgeClass()
        : ($attendance->is_open ? 'badge-green' : 'badge-red');

    $statusClass = match ($rawStatusClass) {
        'badge-green' => 'status-success',
        'badge-blue' => 'status-info',
        'badge-red' => 'status-danger',
        'badge-yellow' => 'status-warning',
        default => 'status-muted',
    };

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

    $backUrl = $attendance->kelas && Route::has('assistant.courses.show')
        ? route('assistant.courses.show', $attendance->kelas)
        : route('assistant.attendances.index');
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Asisten Praktikum</div>

    <h1>Kelola Absensi</h1>

    <p>
        Kelola status kehadiran mahasiswa berdasarkan sesi absensi yang sudah dijadwalkan.
        Kamu dapat mengubah status mahasiswa menjadi hadir, izin, atau alpha.
    </p>

    <div class="hero-actions">
        <a href="{{ $backUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('assistant.attendances.index'))
            <a href="{{ route('assistant.attendances.index') }}" class="btn btn-primary">
                ✅ Semua Absensi
            </a>
        @endif
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Informasi Sesi Absensi</h2>
            <div class="section-subtitle">
                Detail mata kuliah, kelas, jadwal buka, jadwal tutup, dan status sesi.
            </div>
        </div>

        <span class="status-pill {{ $statusClass }}">
            {{ $statusText }}
        </span>
    </div>

    <div class="grid grid-4">
        <div class="stat-card">
            <div class="stat-label">Mata Kuliah</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $courseName }}
            </div>

            <div class="stat-note">
                {{ $courseCode ? 'Kode: '.$courseCode : 'Kode mata kuliah belum tersedia.' }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Kelas</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $className }}
            </div>

            <div class="stat-note">
                {{ $semesterName ? 'Semester '.$semesterName : 'Semester belum tersedia.' }}
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Dibuka</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $openedAt }}
            </div>

            <div class="stat-note">
                Waktu mulai mahasiswa bisa melakukan check-in.
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Ditutup</div>
            <div class="stat-value" style="font-size: 20px;">
                {{ $closedAt }}
            </div>

            <div class="stat-note">
                Waktu akhir mahasiswa bisa melakukan check-in.
            </div>
        </div>
    </div>

    <div style="margin-top: 18px;">
        <div class="actions-inline">
            @if($isWaitingToOpen)
                <span class="status-pill status-info">
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
                <span class="status-pill status-danger">
                    Absensi sudah ditutup
                </span>
            @endif
        </div>
    </div>
</section>

<div class="grid grid-4" style="margin-bottom: 18px;">
    <div class="stat-card">
        <div class="stat-label">Total Mahasiswa</div>
        <div class="stat-value">{{ $totalRecords }}</div>
        <div class="stat-note">Total record absensi pada sesi ini.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Hadir</div>
        <div class="stat-value">{{ $totalHadir }}</div>
        <div class="stat-note">Mahasiswa dengan status hadir.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Izin</div>
        <div class="stat-value">{{ $totalIzin }}</div>
        <div class="stat-note">Mahasiswa dengan status izin.</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Alpha</div>
        <div class="stat-value">{{ $totalAlpha }}</div>
        <div class="stat-note">Mahasiswa yang belum hadir atau tidak mengisi.</div>
    </div>
</div>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Kehadiran Mahasiswa</h2>
            <div class="section-subtitle">
                Ubah status kehadiran mahasiswa jika diperlukan.
            </div>
        </div>
    </div>

    @if($attendance->records->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">✅</div>

            <h3 class="empty-state-title">
                Belum ada record absensi
            </h3>

            <p class="empty-state-text">
                Record mahasiswa akan tampil di sini setelah sesi absensi dibuat.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
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
                        @foreach($attendance->records as $record)
                            @php
                                $recordStatusClass = match ($record->status) {
                                    'hadir' => 'status-success',
                                    'izin' => 'status-info',
                                    default => 'status-danger',
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
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $record->student?->name ?? '-' }}
                                        </strong>

                                        @if($record->student?->nim_nip)
                                            <span class="item-meta" style="margin-top: 0;">
                                                {{ $record->student->nim_nip }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <span class="status-pill status-muted">
                                        {{ $record->student?->student_group ? 'Kelas '.$record->student->student_group : '-' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill {{ $recordStatusClass }}">
                                        {{ $recordStatusLabel }}
                                    </span>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $checkedAt }}
                                    </span>
                                </td>

                                <td>
                                    <form
                                        action="{{ route('assistant.attendances.records.update', ['attendance' => $attendance, 'record' => $record]) }}"
                                        method="POST"
                                        class="actions-inline"
                                    >
                                        @csrf
                                        @method('PATCH')

                                        <select class="form-control" name="status" style="width: 130px;">
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
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</section>
@endsection