@extends('layouts.app')

@section('title', 'Absensi Praktikum')

@section('content')
<section class="dashboard-hero">
    <div class="eyebrow">Asisten Praktikum</div>

    <h1>Absensi Praktikum</h1>

    <p>
        Kelola sesi absensi berdasarkan kelas praktikum, waktu dibuka, dan waktu ditutup.
        Dari halaman ini kamu bisa masuk ke detail absensi untuk mengatur status kehadiran mahasiswa.
    </p>

    <div class="hero-actions">
        <a href="{{ route('assistant.attendances.create') }}" class="btn btn-primary">
            + Buat Sesi Absensi
        </a>

        @if(\Illuminate\Support\Facades\Route::has('assistant.dashboard'))
            <a href="{{ route('assistant.dashboard') }}" class="btn">
                ← Dashboard
            </a>
        @endif
    </div>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Sesi Absensi</h2>
            <div class="section-subtitle">
                Sesi absensi ditampilkan berdasarkan kelas praktikum yang kamu kelola.
            </div>
        </div>

        <a href="{{ route('assistant.attendances.create') }}" class="btn btn-primary btn-sm">
            + Buat Sesi Absensi
        </a>
    </div>

    @if($attendances->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">✅</div>

            <h3 class="empty-state-title">
                Belum ada sesi absensi
            </h3>

            <p class="empty-state-text">
                Sesi absensi yang kamu buat akan tampil di halaman ini.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
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
                        @foreach($attendances as $attendance)
                            @php
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

                                $statusLabel = method_exists($attendance, 'statusLabel')
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

                                $recordsCount = $attendance->records_count ?? $attendance->records->count();
                            @endphp

                            <tr>
                                <td>
                                    <div style="display: grid; gap: 6px;">
                                        <strong>
                                            {{ $courseName }}
                                        </strong>

                                        <div style="display: flex; align-items: center; gap: 6px; flex-wrap: wrap;">
                                            @if($courseCode)
                                                <span class="course-code">
                                                    {{ $courseCode }}
                                                </span>
                                            @endif

                                            <span class="status-pill status-muted">
                                                {{ $className }}
                                            </span>
                                        </div>

                                        @if($semesterName)
                                            <span class="item-meta" style="margin-top: 0;">
                                                Semester {{ $semesterName }}
                                            </span>
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
                                    <span class="status-pill {{ $statusClass }}">
                                        {{ $statusLabel }}
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill status-info">
                                        {{ $recordsCount }} Record
                                    </span>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $attendance->opener?->name ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="actions-inline">
                                        <a
                                            class="btn btn-sm"
                                            href="{{ route('assistant.attendances.show', $attendance) }}"
                                        >
                                            Kelola
                                        </a>

                                        @include('partials.delete-button', [
                                            'action' => route('assistant.attendances.destroy', $attendance)
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
            {{ $attendances->links() }}
        </div>
    @endif
</section>
@endsection