@extends('layouts.app')
@section('title', 'Buat Tugas')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Buat Tugas', 'description' => 'Buat tugas baru dan atur deadline pengumpulan.'])
@if(($classes ?? collect())->isEmpty())<div class="alert alert-error">Kamu belum ditugaskan ke kelas mana pun. Minta admin mengatur kelas praktikum terlebih dahulu.</div>@endif
<form action="{{ route('assistant.tugas.store') }}" method="POST" enctype="multipart/form-data" class="form-card">
    @csrf
    @include('assistant.assignments._form')
    @include('partials.form.actions', ['cancel' => route('assistant.tugas.index'), 'label' => 'Simpan Tugas'])
</form>
@endsection
