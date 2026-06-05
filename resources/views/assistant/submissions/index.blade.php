@extends('layouts.app')

@section('title', 'Submission Mahasiswa')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $timezone = config('app.timezone', 'Asia/Jakarta');

    $dashboardUrl = Route::has('assistant.dashboard')
        ? route('assistant.dashboard')
        : '#';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Asisten Praktikum</div>

    <h1>Submission Mahasiswa</h1>

    <p>
        Lihat pengumpulan tugas mahasiswa, cek file yang dikirim, lalu input nilai dan feedback
        sesuai tugas praktikum yang tersedia.
    </p>

    <div class="hero-actions">
        <a href="{{ $dashboardUrl }}" class="btn">
            ← Dashboard
        </a>

        @if(Route::has('assistant.tugas.index'))
            <a href="{{ route('assistant.tugas.index') }}" class="btn btn-primary">
                📝 Semua Tugas
            </a>
        @endif
    </div>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Submission</h2>
            <div class="section-subtitle">
                Gunakan filter untuk melihat submission yang sudah atau belum dinilai.
            </div>
        </div>

        <form method="GET" class="actions-inline">
            <select class="form-control" name="status" style="width: 190px;">
                <option value="">Semua status</option>
                <option value="graded" @selected(request('status') === 'graded')>
                    Sudah dinilai
                </option>
                <option value="ungraded" @selected(request('status') === 'ungraded')>
                    Belum dinilai
                </option>
            </select>

            <button class="btn" type="submit">
                Filter
            </button>

            @if(request()->filled('status'))
                <a href="{{ route('assistant.submissions.index') }}" class="btn">
                    Reset
                </a>
            @endif
        </form>
    </div>

    @if($submissions->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">📥</div>

            <h3 class="empty-state-title">
                Belum ada submission
            </h3>

            <p class="empty-state-text">
                Submission mahasiswa akan tampil di halaman ini setelah mahasiswa mengumpulkan tugas.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Tugas</th>
                            <th>Dikumpulkan</th>
                            <th>Nilai</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($submissions as $submission)
                            @php
                                $submittedAt = $submission->submitted_at
                                    ? $submission->submitted_at->timezone($timezone)->format('d M Y H:i') . ' WIB'
                                    : '-';

                                $isGraded = $submission->score !== null;
                            @endphp

                            <tr>
                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $submission->student?->name ?? 'Mahasiswa tidak ditemukan' }}
                                        </strong>

                                        @if($submission->student?->nim_nip)
                                            <span class="item-meta" style="margin-top: 0;">
                                                {{ $submission->student->nim_nip }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $submission->assignment?->title ?? 'Tugas tidak ditemukan' }}
                                        </strong>

                                        @if($submission->assignment?->kelas?->course?->name)
                                            <span class="item-meta" style="margin-top: 0;">
                                                {{ $submission->assignment->kelas->course->name }}

                                                @if($submission->assignment?->kelas?->name)
                                                    · {{ $submission->assignment->kelas->name }}
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ $submittedAt }}
                                    </span>
                                </td>

                                <td>
                                    @if($isGraded)
                                        <span class="status-pill status-success">
                                            {{ $submission->score }}
                                        </span>
                                    @else
                                        <span class="status-pill status-warning">
                                            Belum dinilai
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <span class="status-pill {{ $isGraded ? 'status-success' : 'status-warning' }}">
                                        {{ $isGraded ? 'Sudah dinilai' : 'Perlu dinilai' }}
                                    </span>
                                </td>

                                <td>
                                    <a
                                        class="btn btn-sm btn-primary"
                                        href="{{ route('assistant.submissions.show', $submission) }}"
                                    >
                                        Nilai
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 18px;">
            {{ $submissions->links() }}
        </div>
    @endif
</section>
@endsection