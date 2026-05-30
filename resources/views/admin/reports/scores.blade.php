@extends('layouts.app')

@section('title', 'Laporan Nilai')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Laporan',
    'title' => 'Laporan Nilai',
    'description' => 'Pantau nilai submission mahasiswa berdasarkan semester, mata kuliah, kelas, dan status penilaian.'
])

<div class="grid grid-4" style="margin-bottom:18px;">
    @include('partials.stat-card', [
        'label' => 'Total Submission',
        'value' => $statistics['total_submissions'] ?? 0,
        'icon' => '📥'
    ])

    @include('partials.stat-card', [
        'label' => 'Sudah Dinilai',
        'value' => $statistics['graded_submissions'] ?? 0,
        'icon' => '✅'
    ])

    @include('partials.stat-card', [
        'label' => 'Belum Dinilai',
        'value' => $statistics['ungraded_submissions'] ?? 0,
        'icon' => '⏳'
    ])

    @include('partials.stat-card', [
        'label' => 'Rata-rata Nilai',
        'value' => $statistics['average_score'] ?? 0,
        'icon' => '📊'
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
            placeholder="Cari mahasiswa/tugas"
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

        <select class="form-control" style="width:170px;" name="grading_status">
            <option value="">Semua status</option>
            <option value="graded" @selected(request('grading_status') === 'graded')>
                Sudah dinilai
            </option>
            <option value="ungraded" @selected(request('grading_status') === 'ungraded')>
                Belum dinilai
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

        @if(request()->hasAny(['search', 'study_semester_id', 'course_id', 'class_id', 'grading_status', 'date_from', 'date_to']))
            <a href="{{ route('admin.reports.scores') }}" class="btn">
                Reset
            </a>
        @endif
    </form>

    <a
        href="{{ route('admin.reports.scores.export', request()->query()) }}"
        class="btn btn-primary"
    >
        Export CSV
    </a>
</div>

<div class="table-card">
    <table>
        <thead>
            <tr>
                <th>Mahasiswa</th>
                <th>Semester</th>
                <th>Mata Kuliah / Kelas</th>
                <th>Tugas</th>
                <th>Nilai</th>
                <th>Status</th>
                <th>Feedback</th>
                <th>Dikumpulkan</th>
            </tr>
        </thead>

        <tbody>
            @forelse($submissions as $submission)
                <tr>
                    <td>
                        <strong>{{ $submission->student?->name ?? '-' }}</strong>
                        <br>
                        <small>
                            {{ $submission->student?->nim_nip ?? '-' }}
                        </small>
                    </td>

                    <td>
                        {{ $submission->student?->studySemester?->name ?? '-' }}
                    </td>

                    <td>
                        <strong>
                            {{ $submission->assignment?->kelas?->course?->code ?? '-' }}
                            -
                            {{ $submission->assignment?->kelas?->course?->name ?? '-' }}
                        </strong>
                        <br>
                        <small>
                            {{ $submission->assignment?->kelas?->name ?? '-' }}
                        </small>
                    </td>

                    <td>
                        {{ $submission->assignment?->title ?? '-' }}
                        <br>
                        <small>
                            Maks: {{ $submission->assignment?->max_score ?? '-' }}
                        </small>
                    </td>

                    <td>
                        <strong>
                            {{ $submission->score ?? '-' }}
                        </strong>
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

                    <td>
                        {{ $submission->feedback ?? '-' }}
                    </td>

                    <td>
                        {{ $submission->submitted_at?->format('d M Y H:i') ?? '-' }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8">
                        Belum ada data nilai sesuai filter.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">
    {{ $submissions->links() }}
</div>
@endsection