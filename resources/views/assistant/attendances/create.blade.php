@extends('layouts.app')

@section('title', 'Buat Absensi')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $selectedClass = $selectedClass ?? null;
    $classes = $classes ?? collect();

    if ($selectedClass && Route::has('assistant.courses.show')) {
        $cancelUrl = route('assistant.courses.show', $selectedClass);
    } elseif (Route::has('assistant.courses.index')) {
        $cancelUrl = route('assistant.courses.index');
    } else {
        $cancelUrl = Route::has('assistant.attendances.index')
            ? route('assistant.attendances.index')
            : route('assistant.dashboard');
    }

    $timezone = config('app.timezone', 'Asia/Jakarta');
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Asisten Praktikum</div>

    <h1>Buat Sesi Absensi</h1>

    <p>
        Tentukan kelas praktikum, waktu absensi dibuka, dan waktu absensi ditutup.
        Mahasiswa hanya dapat melakukan check-in pada rentang waktu yang sudah ditentukan.
    </p>

    <div class="hero-actions">
        <a href="{{ $cancelUrl }}" class="btn">
            ← Kembali
        </a>

        @if(Route::has('assistant.attendances.index'))
            <a href="{{ route('assistant.attendances.index') }}" class="btn btn-primary">
                ✅ Semua Absensi
            </a>
        @endif
    </div>
</section>

@if($classes->isEmpty() && ! $selectedClass)
    <div class="alert alert-error">
        Kamu belum ditugaskan ke kelas mana pun. Minta admin mengatur kelas praktikum terlebih dahulu.
    </div>
@endif

<div class="alert">
    <strong>Catatan:</strong>
    Satu kelas praktikum hanya boleh memiliki satu sesi absensi dalam satu hari.
    Mahasiswa hanya bisa melakukan check-in pada rentang waktu dibuka sampai waktu ditutup.
</div>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Form Sesi Absensi</h2>
            <div class="section-subtitle">
                Isi informasi sesi absensi sesuai jadwal praktikum.
            </div>
        </div>
    </div>

    <form action="{{ route('assistant.attendances.store') }}" method="POST" class="form-card">
        @csrf

        <div class="form-grid">
            @if($selectedClass)
                <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">

                <div class="alert" style="grid-column: 1 / -1; margin-bottom: 0;">
                    <strong>Mata Kuliah:</strong>
                    {{ $selectedClass->course?->name ?? 'Mata kuliah tidak tersedia' }}

                    @if($selectedClass->course?->code)
                        ({{ $selectedClass->course->code }})
                    @endif

                    · {{ $selectedClass->name }}

                    @if($selectedClass->course?->studySemester)
                        · {{ $selectedClass->course->studySemester->name }}
                    @endif
                </div>
            @else
                <div class="form-group">
                    <label for="class_id" class="form-label">
                        Kelas Praktikum <span class="required">*</span>
                    </label>

                    <select
                        id="class_id"
                        name="class_id"
                        class="form-control"
                        required
                        @disabled($classes->isEmpty())
                    >
                        <option value="">Pilih kelas praktikum</option>

                        @foreach($classes as $class)
                            @php
                                $courseName = $class->course?->name ?? 'Mata kuliah tidak tersedia';
                                $courseCode = $class->course?->code;
                                $semesterName = $class->course?->studySemester?->name;
                            @endphp

                            <option value="{{ $class->id }}" @selected((string) old('class_id') === (string) $class->id)>
                                {{ $courseName }}
                                @if($courseCode)
                                    ({{ $courseCode }})
                                @endif
                                - {{ $class->name }}
                                @if($semesterName)
                                    - {{ $semesterName }}
                                @endif
                            </option>
                        @endforeach
                    </select>

                    <div class="form-help">
                        Pilih kelas praktikum yang akan dibuatkan sesi absensi.
                    </div>

                    @error('class_id')
                        <div class="form-help" style="color: var(--danger);">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            @endif

            <div class="form-group">
                <label for="opened_at" class="form-label">
                    Tanggal & Jam Dibuka <span class="required">*</span>
                </label>

                <input
                    id="opened_at"
                    type="datetime-local"
                    name="opened_at"
                    class="form-control"
                    value="{{ old('opened_at', now($timezone)->format('Y-m-d\TH:i')) }}"
                    required
                >

                <div class="form-help">
                    Mahasiswa baru bisa check-in setelah waktu ini.
                </div>

                @error('opened_at')
                    <div class="form-help" style="color: var(--danger);">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label for="closed_at" class="form-label">
                    Tanggal & Jam Ditutup <span class="required">*</span>
                </label>

                <input
                    id="closed_at"
                    type="datetime-local"
                    name="closed_at"
                    class="form-control"
                    value="{{ old('closed_at', now($timezone)->addHours(2)->format('Y-m-d\TH:i')) }}"
                    required
                >

                <div class="form-help">
                    Setelah waktu ini, mahasiswa tidak bisa check-in lagi.
                </div>

                @error('closed_at')
                    <div class="form-help" style="color: var(--danger);">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ $cancelUrl }}" class="btn">
                Batal
            </a>

            <button
                type="submit"
                class="btn btn-primary"
                @disabled($classes->isEmpty() && ! $selectedClass)
            >
                Buat Absensi
            </button>
        </div>
    </form>
</section>
@endsection