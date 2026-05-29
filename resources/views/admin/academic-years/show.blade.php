@extends('layouts.app')
@section('title', 'Detail Tahun Akademik')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Detail Tahun Akademik'])
<div class="form-card">
    <p><strong>Tahun:</strong> {{ $academicYear->year }}</p>
    <p><strong>Semester:</strong> {{ ucfirst($academicYear->semester) }}</p>
    <p><strong>Status:</strong> {{ $academicYear->is_active ? 'Aktif' : 'Nonaktif' }}</p>
    <div class="form-actions"><a class="btn" href="{{ route('admin.tahun-akademik.index') }}">Kembali</a><a class="btn btn-primary" href="{{ route('admin.tahun-akademik.edit', $academicYear) }}">Edit</a></div>
</div>
@endsection
