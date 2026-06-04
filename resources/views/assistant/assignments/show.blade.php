@extends('layouts.app')

@section('title', 'Detail Tugas')

@section('content')
@php
    $backUrl = $assignment->kelas
        ? route('assistant.courses.show', $assignment->kelas)
        : route('assistant.tugas.index');
@endphp

@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Detail Tugas'])

<div class="form-card">
    <p><strong>Judul:</strong> {{ $assignment->title }}</p>
    <p><strong>Mata Kuliah:</strong> {{ $assignment->kelas?->course?->name }} - {{ $assignment->kelas?->name }}</p>
    <p><strong>Deadline:</strong> {{ optional($assignment->deadline)->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y H:i') }} WIB</p>
    <p><strong>Nilai Maksimal:</strong> {{ $assignment->max_score }}</p>
    <p><strong>Deskripsi:</strong><br>{{ $assignment->description ?? '-' }}</p>

    @if($assignment->file_path)
        <p>
            <a style="color:var(--primary);font-weight:700;" target="_blank" href="{{ asset('storage/'.$assignment->file_path) }}">
                Download lampiran
            </a>
        </p>
    @endif

    <div class="form-actions">
        <a class="btn" href="{{ $backUrl }}">Kembali</a>
        <a class="btn btn-primary" href="{{ route('assistant.tugas.edit', $assignment) }}">Edit</a>
    </div>
</div>
@endsection
