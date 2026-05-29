@extends('layouts.app')
@section('title', 'Detail Pengumuman')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Detail Pengumuman'])
<div class="form-card">
    <p><strong>Judul:</strong> {{ $announcement->title }}</p>
    <p><strong>Kelas:</strong> {{ $announcement->kelas?->course?->name }} - {{ $announcement->kelas?->name }}</p>
    <p><strong>Isi:</strong><br>{{ $announcement->content }}</p>
    <div class="form-actions"><a class="btn" href="{{ route('assistant.pengumuman.index') }}">Kembali</a><a class="btn btn-primary" href="{{ route('assistant.pengumuman.edit', $announcement) }}">Edit</a></div>
</div>
@endsection
