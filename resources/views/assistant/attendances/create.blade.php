@extends('layouts.app', ['title' => 'Buat Absensi'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Buat Sesi Absensi'])
<form action="{{ route('assistant.attendances.store') }}" method="POST" class="rounded-3xl border bg-white p-6 shadow-sm">@csrf<div class="grid gap-5 md:grid-cols-2">@include('partials.form.select', ['label' => 'Kelas', 'name' => 'class_id', 'required' => true])@foreach($classes as $class)<option value="{{ $class->id }}">{{ $class->course?->name }} - {{ $class->name }}</option>@endforeach</select>@include('partials.form.input', ['label' => 'Tanggal Sesi', 'name' => 'session_date', 'type' => 'date', 'value' => now()->format('Y-m-d'), 'required' => true])</div><div class="mt-5">@include('partials.form.checkbox', ['label' => 'Buka absensi sekarang', 'name' => 'open_now', 'checked' => true])</div>@include('partials.form.actions', ['cancel' => route('assistant.attendances.index'), 'label' => 'Buat Sesi'])</form>
@endsection
