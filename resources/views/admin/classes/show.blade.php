@extends('layouts.app')
@section('title', 'Detail Kelas')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Detail Kelas Praktikum'])
<div class="form-card">
    <p><strong>Kelas:</strong> {{ $praktikumClass->name }}</p>
    <p><strong>Matakuliah:</strong> {{ $praktikumClass->course?->name }}</p>
    <p><strong>Asisten:</strong> {{ $praktikumClass->assistant?->name ?? '-' }}</p>
    <p><strong>Ruangan:</strong> {{ $praktikumClass->room ?? '-' }}</p>
    <p><strong>Jadwal:</strong> {{ $praktikumClass->schedule ?? '-' }}</p>
    <div class="form-actions"><a class="btn" href="{{ route('admin.kelas.index') }}">Kembali</a><a class="btn btn-primary" href="{{ route('admin.kelas.edit', $praktikumClass) }}">Edit</a></div>
</div>
@endsection
