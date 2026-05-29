@extends('layouts.app')
@section('title', 'Buat Absensi')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Buat Sesi Absensi', 'description' => 'Buat sesi absensi untuk kelas praktikum.'])
@if(($classes ?? collect())->isEmpty())<div class="alert alert-error">Kamu belum ditugaskan ke kelas mana pun. Minta admin mengatur kelas praktikum terlebih dahulu.</div>@endif
<form action="{{ route('assistant.attendances.store') }}" method="POST" class="form-card">
    @csrf
    <div class="form-grid">
        @include('partials.form.select', ['label' => 'Kelas', 'name' => 'class_id', 'required' => true])
            @foreach(($classes ?? collect()) as $class)
                <option value="{{ $class->id }}" @selected((string) old('class_id') === (string) $class->id)>{{ $class->course?->name }} - {{ $class->name }}</option>
            @endforeach
        </select></label>
        @include('partials.form.input', ['label' => 'Tanggal Sesi', 'name' => 'session_date', 'type' => 'date', 'value' => old('session_date', now()->format('Y-m-d')), 'required' => true])
    </div>
    @include('partials.form.checkbox', ['label' => 'Buka absensi sekarang', 'name' => 'open_now', 'checked' => true])
    @include('partials.form.actions', ['cancel' => route('assistant.attendances.index'), 'label' => 'Buat Sesi'])
</form>
@endsection
