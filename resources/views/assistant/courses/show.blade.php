@extends('layouts.app')

@section('title', 'Kelola Mata Kuliah')

@section('content')
@php
    $course = $class->course;
    $semester = $course?->studySemester;
    $timezone = config('app.timezone', 'Asia/Jakarta');
@endphp

@include('partials.page-header', [
    'eyebrow' => 'Asisten',
    'title' => $course?->name ?? 'Mata Kuliah',
    'description' => trim(($course?->code ? $course->code.' · ' : '').$class->name.($semester ? ' · '.$semester->name : '')),
])

<div class="form-card" style="margin-bottom:18px;">
    <div style="display:flex;justify-content:space-between;gap:16px;align-items:flex-start;flex-wrap:wrap;">
        <div>
            <h2 style="margin:0 0 8px;">Ruang Kelola Kelas</h2>
            <p style="margin:0;color:var(--muted);line-height:1.5;">
                Semua data di bawah ini hanya untuk mata kuliah dan kelas yang sedang dipilih.
                Asisten tidak perlu memilih kelas lagi saat membuat materi, tugas, atau absensi dari halaman ini.
            </p>
            <p style="margin:10px 0 0;color:var(--muted);">
                Ruang: {{ $class->room ?: '-' }} · Jadwal: {{ $class->schedule ?: '-' }}
            </p>
        </div>
        <div class="actions-inline">
            <a href="{{ route('assistant.courses.index') }}" class="btn">← Mata Kuliah</a>
        </div>
    </div>
</div>

<div class="grid grid-4" style="margin-bottom:18px;">
    @include('partials.stat-card', ['label' => 'Mahasiswa', 'value' => $statistics['total_mahasiswa'] ?? 0, 'icon' => '🎓'])
    @include('partials.stat-card', ['label' => 'Materi', 'value' => $statistics['total_materi'] ?? 0, 'icon' => '📘'])
    @include('partials.stat-card', ['label' => 'Tugas', 'value' => $statistics['total_tugas'] ?? 0, 'icon' => '📝'])
    @include('partials.stat-card', ['label' => 'Absensi', 'value' => $statistics['total_absensi'] ?? 0, 'icon' => '✅'])
</div>

<div class="grid grid-3" style="margin-bottom:22px;">
    @include('partials.action-card', [
        'title' => 'Tambah Materi',
        'description' => 'Upload PDF atau simpan link materi untuk kelas ini.',
        'href' => route('assistant.materi.create', ['class_id' => $class->id]),
        'icon' => '📘',
    ])
    @include('partials.action-card', [
        'title' => 'Buat Tugas',
        'description' => 'Buat tugas dan deadline khusus kelas ini.',
        'href' => route('assistant.tugas.create', ['class_id' => $class->id]),
        'icon' => '📝',
    ])
    @include('partials.action-card', [
        'title' => 'Buat Absensi',
        'description' => 'Jadwalkan waktu buka dan tutup absensi kelas ini.',
        'href' => route('assistant.attendances.create', ['class_id' => $class->id]),
        'icon' => '✅',
    ])
</div>

<div class="table-card" style="margin-bottom:22px;">
    <div class="toolbar" style="padding:0 0 14px;">
        <h3 style="margin:0;">Materi</h3>
        <a href="{{ route('assistant.materi.create', ['class_id' => $class->id]) }}" class="btn btn-primary btn-sm">+ Materi</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Judul</th>
                <th>Tipe</th>
                <th>Publikasi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($materials as $material)
                <tr>
                    <td>
                        <strong>{{ $material->title }}</strong><br>
                        <small>{{ str($material->description)->limit(90) ?: '-' }}</small>
                    </td>
                    <td>{{ strtoupper($material->type) }}</td>
                    <td>{{ $material->published_at ? $material->published_at->timezone($timezone)->format('d M Y H:i').' WIB' : '-' }}</td>
                    <td class="actions-inline">
                        <a href="{{ route('assistant.materi.show', $material) }}" class="btn btn-sm">Detail</a>
                        <a href="{{ route('assistant.materi.edit', $material) }}" class="btn btn-sm">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">Belum ada materi untuk kelas ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="table-card" style="margin-bottom:22px;">
    <div class="toolbar" style="padding:0 0 14px;">
        <h3 style="margin:0;">Tugas</h3>
        <a href="{{ route('assistant.tugas.create', ['class_id' => $class->id]) }}" class="btn btn-primary btn-sm">+ Tugas</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Judul</th>
                <th>Deadline</th>
                <th>Submission</th>
                <th>Nilai Maks</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assignments as $assignment)
                <tr>
                    <td>
                        <strong>{{ $assignment->title }}</strong><br>
                        <small>{{ str($assignment->description)->limit(90) ?: '-' }}</small>
                    </td>
                    <td>{{ $assignment->deadline ? $assignment->deadline->timezone($timezone)->format('d M Y H:i').' WIB' : '-' }}</td>
                    <td>{{ $assignment->submissions_count ?? 0 }}</td>
                    <td>{{ $assignment->max_score }}</td>
                    <td class="actions-inline">
                        <a href="{{ route('assistant.tugas.show', $assignment) }}" class="btn btn-sm">Detail</a>
                        <a href="{{ route('assistant.tugas.edit', $assignment) }}" class="btn btn-sm">Edit</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">Belum ada tugas untuk kelas ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="table-card" style="margin-bottom:22px;">
    <div class="toolbar" style="padding:0 0 14px;">
        <h3 style="margin:0;">Absensi</h3>
        <a href="{{ route('assistant.attendances.create', ['class_id' => $class->id]) }}" class="btn btn-primary btn-sm">+ Absensi</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>Waktu</th>
                <th>Status</th>
                <th>Record</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $attendance)
                @php
                    $statusLabel = method_exists($attendance, 'statusLabel') ? $attendance->statusLabel() : ($attendance->is_open ? 'Sedang Dibuka' : 'Ditutup');
                    $statusClass = method_exists($attendance, 'statusBadgeClass') ? $attendance->statusBadgeClass() : ($attendance->is_open ? 'badge-green' : 'badge-red');
                @endphp
                <tr>
                    <td>
                        <strong>Dibuka:</strong>
                        {{ $attendance->opened_at ? $attendance->opened_at->timezone($timezone)->format('d M Y H:i').' WIB' : '-' }}
                        <br>
                        <strong>Ditutup:</strong>
                        {{ $attendance->closed_at ? $attendance->closed_at->timezone($timezone)->format('d M Y H:i').' WIB' : '-' }}
                    </td>
                    <td><span class="badge {{ $statusClass }}">{{ $statusLabel }}</span></td>
                    <td>{{ $attendance->records_count ?? 0 }}</td>
                    <td>
                        <a href="{{ route('assistant.attendances.show', $attendance) }}" class="btn btn-sm">Kelola</a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4">Belum ada absensi untuk kelas ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="table-card">
    <h3 style="margin:0 0 14px;">Submission Terbaru</h3>

    <table>
        <thead>
            <tr>
                <th>Mahasiswa</th>
                <th>Tugas</th>
                <th>Dikumpulkan</th>
                <th>Nilai</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($latestSubmissions as $submission)
                <tr>
                    <td>{{ $submission->student?->name ?? '-' }}</td>
                    <td>{{ $submission->assignment?->title ?? '-' }}</td>
                    <td>{{ $submission->submitted_at ? $submission->submitted_at->timezone($timezone)->format('d M Y H:i').' WIB' : '-' }}</td>
                    <td>{{ $submission->score ?? 'Belum dinilai' }}</td>
                    <td><a href="{{ route('assistant.submissions.show', $submission) }}" class="btn btn-sm btn-primary">Nilai</a></td>
                </tr>
            @empty
                <tr><td colspan="5">Belum ada submission untuk kelas ini.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
