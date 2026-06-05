@extends('layouts.app')

@section('title', 'Tugas Praktikum')

@section('content')
<section class="dashboard-hero">
    <div class="eyebrow">Mahasiswa</div>

    <h1>Tugas Praktikum</h1>

    <p>
        Lihat daftar tugas praktikum dari kelas yang kamu ikuti.
        Perhatikan deadline dan status pengumpulan agar tidak ada tugas yang terlewat.
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

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Tugas</h2>
            <div class="section-subtitle">
                Tugas ditampilkan berdasarkan kelas praktikum yang dapat kamu akses.
            </div>
        </div>
    </div>

    @if($assignments->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">📝</div>

            <h3 class="empty-state-title">
                Belum ada tugas
            </h3>

            <p class="empty-state-text">
                Tugas dari asisten akan tampil di halaman ini jika sudah dipublikasikan.
            </p>
        </div>
    @else
        <div class="list-stack">
            @foreach($assignments as $assignment)
                @php
                    $isSubmitted = $assignment->submissions->isNotEmpty();
                    $deadline = $assignment->deadline;
                    $isPastDeadline = $deadline && $deadline->isPast();
                @endphp

                <a
                    href="{{ route('student.assignments.show', $assignment) }}"
                    class="list-item"
                >
                    <div style="min-width: 0;">
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 8px;">
                            <span class="course-code">
                                {{ $assignment->kelas?->course?->code ?? 'Tugas' }}
                            </span>

                            @if($isSubmitted)
                                <span class="status-pill status-success">
                                    Sudah dikumpulkan
                                </span>
                            @else
                                <span class="status-pill {{ $isPastDeadline ? 'status-danger' : 'status-warning' }}">
                                    {{ $isPastDeadline ? 'Deadline lewat' : 'Belum dikumpulkan' }}
                                </span>
                            @endif
                        </div>

                        <h3 class="item-title">
                            {{ $assignment->title }}
                        </h3>

                        <div class="item-meta">
                            {{ $assignment->kelas?->course?->name ?? 'Mata kuliah tidak ditemukan' }}

                            @if($assignment->kelas?->name)
                                · {{ $assignment->kelas->name }}
                            @endif

                            <br>

                            Deadline:
                            <strong>
                                {{ $deadline?->format('d M Y H:i') ?? '-' }}
                            </strong>
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 10px; flex-shrink: 0;">
                        <span class="status-pill status-info">
                            Buka tugas
                        </span>

                        <span style="font-weight: 900; color: var(--primary);">
                            →
                        </span>
                    </div>
                </a>
            @endforeach
        </div>

        <div style="margin-top: 18px;">
            {{ $assignments->links() }}
        </div>
    @endif
</section>
@endsection