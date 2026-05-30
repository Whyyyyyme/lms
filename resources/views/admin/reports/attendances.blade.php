@extends('layouts.app')

@section('title', 'Laporan Absensi')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Laporan',
    'title' => 'Laporan Absensi',
    'description' => 'Pantau sesi absensi dan rekap kehadiran mahasiswa berdasarkan semester, mata kuliah, dan kelas.'
])

<div class="grid grid-4" style="margin-bottom:18px;">
    @include('partials.stat-card', [
        'label' => 'Total Sesi',
        'value' => $statistics['total_sessions'] ?? 0,
        'icon' => '📅'
    ])

    @include('partials.stat-card', [
        'label' => 'Sesi Terbuka',
        'value' => $statistics['open_sessions'] ?? 0,
        'icon' => '🟢'
    ])

    @include('partials.stat-card', [
        'label' => 'Total Record',
        'value' => $statistics['total_records'] ?? 0,
        'icon' => '📝'
    ])

    @include('partials.stat-card', [
        'label' => 'Hadir',
        'value' => $statistics['hadir_records'] ?? 0,
        'icon' => '✅'
    ])
</div>

<div class="grid grid-4" style="margin-bottom:18px;">
    @include('partials.stat-card', [
        'label' => 'Izin',
        'value' => $statistics['izin_records'] ?? 0,
        'icon' => '🟡'
    ])

    @include('partials.stat-card', [
        'label' => 'Alpha',
        'value' => $statistics['alpha_records'] ?? 0,
        'icon' => '🔴'
    ])

    @include('partials.stat-card', [
        'label' => 'Sesi Ditutup',
        'value' => $statistics['closed_sessions'] ?? 0,
        'icon' => '🔒'
    ])
</div>

<div class="toolbar">
    <form method="GET" class="actions-inline">
        <input
            class="form-control"
            style="width:220px;"
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari kelas/matkul/asisten"
        >

        <select class="form-control" style="width:180px;" name="study_semester_id">
            <option value="">Semua semester</option>
            @foreach($studySemesters as $semester)
                <option value="{{ $semester->id }}" @selected((string) request('study_semester_id') === (string) $semester->id)>
                    {{ $semester->name }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width:220px;" name="course_id">
            <option value="">Semua mata kuliah</option>
            @foreach($courses as $course)
                <option value="{{ $course->id }}" @selected((string) request('course_id') === (string) $course->id)>
                    {{ $course->code }} - {{ $course->name }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width:200px;" name="class_id">
            <option value="">Semua kelas</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected((string) request('class_id') === (string) $class->id)>
                    {{ $class->course?->code ?? '-' }} - {{ $class->name }}
                </option>
            @endforeach
        </select>

        <select class="form-control" style="width:160px;" name="session_status">
            <option value="">Semua sesi</option>
            <option value="open" @selected(request('session_status') === 'open')>
                Terbuka
            </option>
            <option value="closed" @selected(request('session_status') === 'closed')>
                Ditutup
            </option>
        </select>

        <select class="form-control" style="width:160px;" name="record_status">
            <option value="">Semua hadir</option>
            <option value="hadir" @selected(request('record_status') === 'hadir')>
                Hadir
            </option>
            <option value="izin" @selected(request('record_status') === 'izin')>
                Izin
            </option>
            <option value="alpha" @selected(request('record_status') === 'alpha')>
                Alpha
            </option>
        </select>

        <input
            class="form-control"
            style="width:160px;"
            type="date"
            name="date_from"
            value="{{ request('date_from') }}"
        >

        <input
            class="form-control"
            style="width:160px;"
            type="date"
            name="date_to"
            value="{{ request('date_to') }}"
        >

        <button class="btn" type="submit">
            Filter
        </button>

        @if(request()->hasAny(['search', 'study_semester_id', 'course_id', 'class_id', 'session_status', 'record_status', 'date_from', 'date_to']))
            <a href="{{ route('admin.reports.attendances') }}" class="btn">
                Reset
            </a>
        @endif
    </form>

    <a
        href="{{ route('admin.reports.attendances.export', request()->query()) }}"
        class="btn btn-primary"
    >
        Export CSV
    </a>
</div>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Semester</th>
                <th>Mata Kuliah / Kelas</th>
                <th>Dibuka Oleh</th>
                <th>Record</th>
                <th>Rekap</th>
                <th>Status Sesi</th>
            </tr>
        </thead>

        <tbody>
            @forelse($attendances as $attendance)
                <tr>
                    <td>
                        <strong>{{ $attendance->session_date?->format('d M Y') ?? '-' }}</strong>
                        <br>
                        <small>
                            Buka: {{ $attendance->opened_at?->format('H:i') ?? '-' }}
                            |
                            Tutup: {{ $attendance->closed_at?->format('H:i') ?? '-' }}
                        </small>
                    </td>

                    <td>
                        {{ $attendance->kelas?->course?->studySemester?->name ?? '-' }}
                    </td>

                    <td>
                        <strong>
                            {{ $attendance->kelas?->course?->code ?? '-' }}
                            -
                            {{ $attendance->kelas?->course?->name ?? '-' }}
                        </strong>
                        <br>
                        <small>
                            {{ $attendance->kelas?->name ?? '-' }}
                        </small>
                    </td>

                    <td>
                        {{ $attendance->opener?->name ?? '-' }}
                    </td>

                    <td>
                        {{ $attendance->records_count }}
                    </td>

                    <td>
                        <small>
                            Hadir: {{ $attendance->hadir_count ?? 0 }}<br>
                            Izin: {{ $attendance->izin_count ?? 0 }}<br>
                            Alpha: {{ $attendance->alpha_count ?? 0 }}
                        </small>
                    </td>

                    <td>
                        <span class="badge {{ $attendance->is_open ? 'badge-green' : 'badge-red' }}">
                            {{ $attendance->is_open ? 'Terbuka' : 'Ditutup' }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">
                        Belum ada data absensi sesuai filter.
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