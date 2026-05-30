@extends('layouts.app')

@section('title', 'Dashboard Admin')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $safe = fn ($name) => Route::has($name) ? route($name) : '#';
    $statistics = $statistics ?? [];
@endphp

@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Dashboard Admin',
    'description' => 'Ringkasan data LMS Praktikum berdasarkan semester, mata kuliah, kelas, user, tugas, dan absensi.'
])

<div class="grid grid-4" style="margin-bottom:18px;">
    @include('partials.stat-card', [
        'label' => 'Asisten',
        'value' => $statistics['total_asisten'] ?? 0,
        'icon' => '🧑‍🏫'
    ])

    @include('partials.stat-card', [
        'label' => 'Mahasiswa',
        'value' => $statistics['total_mahasiswa'] ?? 0,
        'icon' => '🎓'
    ])

    @include('partials.stat-card', [
        'label' => 'Mata Kuliah',
        'value' => $statistics['total_courses'] ?? 0,
        'icon' => '📚'
    ])

    @include('partials.stat-card', [
        'label' => 'Kelas Praktikum',
        'value' => $statistics['total_classes'] ?? 0,
        'icon' => '🏫'
    ])
</div>

<div class="grid grid-4" style="margin-bottom:18px;">
    @include('partials.stat-card', [
        'label' => 'Semester Aktif',
        'value' => ($statistics['total_active_semesters'] ?? 0) . ' / ' . ($statistics['total_semesters'] ?? 0),
        'icon' => '🎯'
    ])

    @include('partials.stat-card', [
        'label' => 'Kelas Aktif',
        'value' => ($statistics['total_active_classes'] ?? 0) . ' / ' . ($statistics['total_classes'] ?? 0),
        'icon' => '✅'
    ])

    @include('partials.stat-card', [
        'label' => 'Submission Belum Dinilai',
        'value' => $statistics['total_ungraded_submissions'] ?? 0,
        'icon' => '📥'
    ])

    @include('partials.stat-card', [
        'label' => 'Absensi Terbuka',
        'value' => $statistics['total_open_attendances'] ?? 0,
        'icon' => '🟢'
    ])
</div>

@if(($statistics['total_mahasiswa_tanpa_semester'] ?? 0) > 0 || ($statistics['total_classes_without_assistant'] ?? 0) > 0)
    <div class="alert alert-error">
        <strong>Perlu diperbaiki:</strong>
        <ul style="margin:8px 0 0 18px;">
            @if(($statistics['total_mahasiswa_tanpa_semester'] ?? 0) > 0)
                <li>
                    Ada {{ $statistics['total_mahasiswa_tanpa_semester'] }} mahasiswa yang belum punya semester.
                    Cek menu <strong>Kelola Asisten & Mahasiswa</strong>.
                </li>
            @endif

            @if(($statistics['total_classes_without_assistant'] ?? 0) > 0)
                <li>
                    Ada {{ $statistics['total_classes_without_assistant'] }} kelas praktikum yang belum punya asisten.
                    Cek menu <strong>Kelola Kelas Praktikum</strong>.
                </li>
            @endif
        </ul>
    </div>
@endif

<div class="grid grid-3" style="margin-bottom:18px;">
    @include('partials.action-card', [
        'title' => 'Tambah Asisten / Mahasiswa',
        'description' => 'Tambah akun asisten praktikum atau mahasiswa. Akun admin utama dikelola manual.',
        'href' => $safe('admin.users.create'),
        'icon' => '👥'
    ])

    @include('partials.action-card', [
        'title' => 'Tambah Semester',
        'description' => 'Buat semester mahasiswa untuk dasar mata kuliah dan kelas praktikum.',
        'href' => $safe('admin.semester.create'),
        'icon' => '🎓'
    ])

    @include('partials.action-card', [
        'title' => 'Tambah Mata Kuliah',
        'description' => 'Buat mata kuliah dan hubungkan ke semester mahasiswa.',
        'href' => $safe('admin.matakuliah.create'),
        'icon' => '📚'
    ])

    @include('partials.action-card', [
        'title' => 'Tambah Kelas',
        'description' => 'Buat kelas praktikum, pilih mata kuliah, dan hubungkan asisten.',
        'href' => $safe('admin.kelas.create'),
        'icon' => '🏫'
    ])

    @include('partials.action-card', [
        'title' => 'Laporan Nilai',
        'description' => 'Lihat rekap nilai submission mahasiswa.',
        'href' => $safe('admin.reports.scores'),
        'icon' => '🧾'
    ])

    @include('partials.action-card', [
        'title' => 'Laporan Absensi',
        'description' => 'Pantau rekap kehadiran mahasiswa.',
        'href' => $safe('admin.reports.attendances'),
        'icon' => '✅'
    ])
</div>

<div class="grid gap-5 md:grid-cols-2">
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Semester</th>
                    <th>Mata Kuliah</th>
                    <th>Mahasiswa</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($semesterSummaries as $semester)
                    <tr>
                        <td>
                            <a href="{{ route('admin.semester.show', $semester) }}">
                                <strong>{{ $semester->name }}</strong>
                            </a>
                            <br>
                            <small>Level {{ $semester->level }}</small>
                        </td>

                        <td>{{ $semester->courses_count }}</td>

                        <td>{{ $semester->students_count }}</td>

                        <td>
                            <span class="badge {{ $semester->is_active ? 'badge-green' : 'badge-red' }}">
                                {{ $semester->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Belum ada data semester.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>User Terbaru</th>
                    <th>Jenis Akun</th>
                    <th>Semester</th>
                </tr>
            </thead>

            <tbody>
                @forelse($latestUsers as $user)
                    @php
                        $currentRole = $user->roles->pluck('name')->first() ?: $user->role;

                        $roleLabel = match ($currentRole) {
                            'asisten' => 'Asisten',
                            'mahasiswa' => 'Mahasiswa',
                            default => ucfirst($currentRole ?? '-'),
                        };
                    @endphp

                    <tr>
                        <td>
                            <a href="{{ route('admin.users.show', $user) }}">
                                <strong>{{ $user->name }}</strong>
                            </a>
                            <br>
                            <small>{{ $user->email }}</small>
                        </td>

                        <td>{{ $roleLabel }}</td>

                        <td>
                            {{ $currentRole === 'mahasiswa' ? ($user->studySemester?->name ?? '-') : '-' }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Belum ada user terbaru.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="grid gap-5 md:grid-cols-2" style="margin-top:18px;">
    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Kelas Terbaru</th>
                    <th>Mata Kuliah</th>
                    <th>Asisten</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
                @forelse($latestClasses as $class)
                    <tr>
                        <td>
                            <a href="{{ route('admin.kelas.show', $class) }}">
                                <strong>{{ $class->name }}</strong>
                            </a>
                            <br>
                            <small>{{ $class->course?->studySemester?->name ?? '-' }}</small>
                        </td>

                        <td>
                            {{ $class->course?->code ?? '-' }}
                            <br>
                            <small>{{ $class->course?->name ?? '-' }}</small>
                        </td>

                        <td>{{ $class->assistant?->name ?? '-' }}</td>

                        <td>
                            <span class="badge {{ $class->is_active ? 'badge-green' : 'badge-red' }}">
                                {{ $class->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">Belum ada kelas praktikum.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Submission Terbaru</th>
                    <th>Tugas</th>
                    <th>Status Nilai</th>
                </tr>
            </thead>

            <tbody>
                @forelse($latestSubmissions as $submission)
                    <tr>
                        <td>
                            <strong>{{ $submission->student?->name ?? '-' }}</strong>
                            <br>
                            <small>
                                {{ $submission->submitted_at?->format('d M Y H:i') ?? '-' }}
                            </small>
                        </td>

                        <td>
                            {{ $submission->assignment?->title ?? '-' }}
                            <br>
                            <small>
                                {{ $submission->assignment?->kelas?->course?->name ?? '-' }}
                            </small>
                        </td>

                        <td>
                            @if($submission->graded_at)
                                <span class="badge badge-green">
                                    Sudah dinilai
                                </span>
                            @else
                                <span class="badge badge-red">
                                    Belum dinilai
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">Belum ada submission.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection