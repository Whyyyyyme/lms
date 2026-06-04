@extends('layouts.app')

@section('title', 'Buat Absensi')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Asisten',
    'title' => 'Buat Sesi Absensi',
    'description' => 'Tentukan tanggal dan jam dibuka serta tanggal dan jam ditutup untuk absensi praktikum.'
])

@if(($classes ?? collect())->isEmpty())
    <div class="alert alert-error">
        Kamu belum ditugaskan ke kelas mana pun. Minta admin mengatur kelas praktikum terlebih dahulu.
    </div>
@endif

<div class="alert" style="margin-bottom:16px;">
    <strong>Catatan:</strong>
    Satu kelas praktikum hanya boleh memiliki satu sesi absensi dalam satu hari.
    Mahasiswa hanya bisa melakukan check-in pada rentang waktu dibuka sampai waktu ditutup.
</div>

<form action="{{ route('assistant.attendances.store') }}" method="POST" class="form-card">
    @csrf

    <div class="form-grid">
        @include('partials.form.select', [
            'label' => 'Kelas Praktikum',
            'name' => 'class_id',
            'required' => true
        ])
            <option value="">Pilih kelas praktikum</option>

            @foreach(($classes ?? collect()) as $class)
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
        </select></label>

        @include('partials.form.input', [
            'label' => 'Tanggal & Jam Dibuka',
            'name' => 'opened_at',
            'type' => 'datetime-local',
            'value' => old('opened_at', now(config('app.timezone', 'Asia/Jakarta'))->format('Y-m-d\TH:i')),
            'required' => true,
            'help' => 'Mahasiswa baru bisa check-in setelah waktu ini.'
        ])

        @include('partials.form.input', [
            'label' => 'Tanggal & Jam Ditutup',
            'name' => 'closed_at',
            'type' => 'datetime-local',
            'value' => old('closed_at', now(config('app.timezone', 'Asia/Jakarta'))->addHours(2)->format('Y-m-d\TH:i')),
            'required' => true,
            'help' => 'Setelah waktu ini, mahasiswa tidak bisa check-in lagi.'
        ])
    </div>

    @include('partials.form.actions', [
        'cancel' => route('assistant.attendances.index'),
        'label' => 'Buat Absensi'
    ])
</form>
@endsection