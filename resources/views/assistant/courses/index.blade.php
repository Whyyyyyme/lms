@extends('layouts.app')

@section('title', 'Mata Kuliah Diajar')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Asisten',
    'title' => 'Mata Kuliah yang Diajar',
    'description' => 'Pilih mata kuliah/kelas terlebih dahulu. Setelah masuk ke kelas, baru kelola materi, tugas, dan absensi.'
])

<div class="grid grid-4" style="margin-bottom:18px;">
    @include('partials.stat-card', ['label' => 'Kelas Diampu', 'value' => $statistics['total_kelas'] ?? 0, 'icon' => '🏫'])
    @include('partials.stat-card', ['label' => 'Mahasiswa', 'value' => $statistics['total_mahasiswa'] ?? 0, 'icon' => '🎓'])
    @include('partials.stat-card', ['label' => 'Materi', 'value' => $statistics['total_materi'] ?? 0, 'icon' => '📘'])
    @include('partials.stat-card', ['label' => 'Tugas', 'value' => $statistics['total_tugas'] ?? 0, 'icon' => '📝'])
</div>

@if(($classes ?? collect())->isEmpty())
    <div class="alert alert-error">
        Kamu belum ditugaskan ke kelas/mata kuliah mana pun. Minta admin mengatur asisten pada menu kelas praktikum.
    </div>
@else
    <div class="grid grid-3">
        @foreach($classes as $class)
            @php
                $course = $class->course;
                $semester = $course?->studySemester;
            @endphp

            <a href="{{ route('assistant.courses.show', $class) }}" class="action-card" style="display:block;padding:20px;">
                <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-start;margin-bottom:12px;">
                    <div>
                        <div style="font-size:12px;font-weight:900;color:var(--primary);text-transform:uppercase;letter-spacing:.06em;">
                            {{ $course?->code ?? 'Kode MK' }}
                        </div>
                        <h3 style="margin:6px 0 4px;font-size:20px;line-height:1.25;">
                            {{ $course?->name ?? 'Mata kuliah tidak tersedia' }}
                        </h3>
                        <p style="margin:0;color:var(--muted);font-size:14px;">
                            {{ $class->name }}
                            @if($semester)
                                · {{ $semester->name }}
                            @endif
                        </p>
                    </div>
                    <div style="font-size:28px;">📚</div>
                </div>

                <div class="grid grid-3" style="gap:8px;margin:14px 0;">
                    <div class="stat-card" style="padding:10px;box-shadow:none;">
                        <small>Materi</small><br><strong>{{ $class->materials_count ?? 0 }}</strong>
                    </div>
                    <div class="stat-card" style="padding:10px;box-shadow:none;">
                        <small>Tugas</small><br><strong>{{ $class->assignments_count ?? 0 }}</strong>
                    </div>
                    <div class="stat-card" style="padding:10px;box-shadow:none;">
                        <small>Absensi</small><br><strong>{{ $class->attendances_count ?? 0 }}</strong>
                    </div>
                </div>

                <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;">
                    <span style="color:var(--muted);font-size:14px;">
                        {{ $class->resolved_students_count ?? 0 }} mahasiswa
                    </span>
                    <span style="font-weight:800;color:var(--primary);">Kelola →</span>
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection
