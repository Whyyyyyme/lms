@extends('layouts.app')
@section('title', 'Detail Tugas')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Detail Tugas'])
<div class="form-card">
    <p><strong>Judul:</strong> {{ $assignment->title }}</p>
    <p><strong>Kelas:</strong> {{ $assignment->kelas?->course?->name }} - {{ $assignment->kelas?->name }}</p>
    <p><strong>Deadline:</strong> {{ optional($assignment->deadline)->format('d M Y H:i') }}</p>
    <p><strong>Nilai Maksimal:</strong> {{ $assignment->max_score }}</p>
    <p><strong>Deskripsi:</strong><br>{{ $assignment->description ?? '-' }}</p>
    @if($assignment->file_path)<p><a style="color:var(--primary);font-weight:700;" target="_blank" href="{{ asset('storage/'.$assignment->file_path) }}">Download lampiran</a></p>@endif
    <div class="form-actions"><a class="btn" href="{{ route('assistant.tugas.index') }}">Kembali</a><a class="btn btn-primary" href="{{ route('assistant.tugas.edit', $assignment) }}">Edit</a></div>
</div>
@endsection
