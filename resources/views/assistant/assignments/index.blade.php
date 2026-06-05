@extends('layouts.app')

@section('title', 'Tugas Praktikum')

@section('content')
<section class="dashboard-hero">
    <div class="eyebrow">Asisten Praktikum</div>

    <h1>Tugas Praktikum</h1>

    <p>
        Kelola tugas praktikum, deadline pengumpulan, nilai maksimal, dan submission mahasiswa.
        Tugas yang dibuat di sini akan dapat diakses oleh mahasiswa sesuai kelas praktikum.
    </p>

    <div class="hero-actions">
        <a href="{{ route('assistant.tugas.create') }}" class="btn btn-primary">
            + Buat Tugas
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
            <h2 class="section-title">Daftar Tugas</h2>
            <div class="section-subtitle">
                Tugas ditampilkan berdasarkan kelas praktikum yang kamu kelola.
            </div>
        </div>

        <a href="{{ route('assistant.tugas.create') }}" class="btn btn-primary btn-sm">
            + Buat Tugas
        </a>
    </div>

    @if($assignments->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">📝</div>

            <h3 class="empty-state-title">
                Belum ada tugas
            </h3>

            <p class="empty-state-text">
                Tugas yang kamu buat akan tampil di halaman ini.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Judul Tugas</th>
                            <th>Kelas</th>
                            <th>Deadline</th>
                            <th>Nilai Maks</th>
                            <th>Submission</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($assignments as $assignment)
                            @php
                                $deadline = $assignment->deadline;
                                $isPastDeadline = $deadline && $deadline->isPast();
                                $submissionCount = $assignment->submissions_count ?? $assignment->submissions->count();
                            @endphp

                            <tr>
                                <td>
                                    <div style="display: grid; gap: 6px;">
                                        <strong>
                                            {{ $assignment->title }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ str($assignment->description)->limit(90) }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <div style="display: grid; gap: 6px;">
                                        <strong>
                                            {{ $assignment->kelas?->course?->name ?? 'Mata kuliah tidak ditemukan' }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ $assignment->kelas?->name ?? 'Kelas tidak ditemukan' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    @if($deadline)
                                        <div style="display: grid; gap: 6px;">
                                            <span class="item-meta" style="margin-top: 0;">
                                                {{ $deadline->format('d M Y H:i') }}
                                            </span>

                                            <span class="status-pill {{ $isPastDeadline ? 'status-danger' : 'status-warning' }}">
                                                {{ $isPastDeadline ? 'Deadline lewat' : 'Aktif' }}
                                            </span>
                                        </div>
                                    @else
                                        <span class="status-pill status-muted">
                                            Belum diatur
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <span class="status-pill status-info">
                                        {{ $assignment->max_score ?? 0 }}
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill status-muted">
                                        {{ $submissionCount }} Submission
                                    </span>
                                </td>

                                <td>
                                    <div class="actions-inline">
                                        <a
                                            class="btn btn-sm"
                                            href="{{ route('assistant.tugas.show', $assignment) }}"
                                        >
                                            Detail
                                        </a>

                                        <a
                                            class="btn btn-sm"
                                            href="{{ route('assistant.tugas.edit', $assignment) }}"
                                        >
                                            Edit
                                        </a>

                                        @include('partials.delete-button', [
                                            'action' => route('assistant.tugas.destroy', $assignment)
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
            {{ $assignments->links() }}
        </div>
    @endif
</section>
@endsection