@extends('layouts.app')
@section('title', 'Detail Matakuliah')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Detail Matakuliah'])
<div class="form-card">
    <p><strong>Kode:</strong> {{ $course->code }}</p>
    <p><strong>Nama:</strong> {{ $course->name }}</p>
    <p><strong>SKS:</strong> {{ $course->sks }}</p>
    <p><strong>Tahun Akademik:</strong> {{ $course->academicYear?->year }} - {{ ucfirst($course->academicYear?->semester ?? '') }}</p>
    <div class="form-actions"><a class="btn" href="{{ route('admin.matakuliah.index') }}">Kembali</a><a class="btn btn-primary" href="{{ route('admin.matakuliah.edit', $course) }}">Edit</a></div>
</div>
@endsection
