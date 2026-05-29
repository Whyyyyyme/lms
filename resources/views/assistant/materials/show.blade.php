@extends('layouts.app')
@section('title', 'Detail Materi')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Detail Materi'])
<div class="form-card">
    <p><strong>Judul:</strong> {{ $material->title }}</p>
    <p><strong>Kelas:</strong> {{ $material->kelas?->course?->name }} - {{ $material->kelas?->name }}</p>
    <p><strong>Tipe:</strong> {{ strtoupper($material->type) }}</p>
    <p><strong>Deskripsi:</strong><br>{{ $material->description ?? '-' }}</p>
    @if($material->file_path)
        <p><strong>File/Link:</strong> @if(str_starts_with($material->file_path, 'http'))<a style="color:var(--primary);font-weight:700;" target="_blank" href="{{ $material->file_path }}">Buka link</a>@else<a style="color:var(--primary);font-weight:700;" target="_blank" href="{{ asset('storage/'.$material->file_path) }}">Download file</a>@endif</p>
    @endif
    <div class="form-actions"><a class="btn" href="{{ route('assistant.materi.index') }}">Kembali</a><a class="btn btn-primary" href="{{ route('assistant.materi.edit', $material) }}">Edit</a></div>
</div>
@endsection
