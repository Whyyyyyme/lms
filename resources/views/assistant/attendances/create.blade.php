@extends('layouts.app')

@section('title', 'Buat Absensi')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Asisten',
    'title' => 'Buat Sesi Absensi',
    'description' => 'Tentukan waktu buka dan tutup absensi untuk kelas praktikum.'
])

@if(($classes ?? collect())->isEmpty())
    <div class="alert alert-error">
        Kamu belum ditugaskan ke kelas mana pun. Minta admin mengatur kelas praktikum terlebih dahulu.
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-error">
        <strong>Ada data yang perlu diperbaiki:</strong>
        <ul style="margin:8px 0 0 18px;">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('assistant.attendances.store') }}" method="POST" class="form-card">
    @csrf

    <div class="form-grid">
        @include('partials.form.select', [
            'label' => 'Kelas',
            'name' => 'class_id',
            'required' => true
        ])
            <option value="">Pilih kelas praktikum</option>
            @foreach(($classes ?? collect()) as $class)
                <option value="{{ $class->id }}" @selected((string) old('class_id') === (string) $class->id)>
                    {{ $class->course?->name }} - {{ $class->name }}
                </option>
            @endforeach
        </select></label>

        @include('partials.form.input', [
            'label' => 'Tanggal & Jam Dibuka',
            'name' => 'opened_at',
            'type' => 'datetime-local',
            'value' => now('Asia/Jakarta')->format('Y-m-d\TH:i'),
            'required' => true,
            'help' => 'Mahasiswa baru bisa check-in setelah waktu ini.'
        ])

        @include('partials.form.input', [
            'label' => 'Tanggal & Jam Ditutup',
            'name' => 'closed_at',
            'type' => 'datetime-local',
            'value' => now('Asia/Jakarta')->addHours(2)->format('Y-m-d\TH:i'),
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
