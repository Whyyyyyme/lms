@extends('layouts.app')
@section('title', 'Upload Materi')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Upload Materi', 'description' => 'Tambahkan materi PDF, dokumen, atau link video untuk kelas praktikum.'])
@if(($classes ?? collect())->isEmpty())<div class="alert alert-error">Kamu belum ditugaskan ke kelas mana pun. Minta admin mengatur kelas praktikum terlebih dahulu.</div>@endif
<form action="{{ route('assistant.materi.store') }}" method="POST" enctype="multipart/form-data" class="form-card">
    @csrf
    @include('assistant.materials._form')
    @include('partials.form.actions', ['cancel' => route('assistant.materi.index'), 'label' => 'Simpan Materi'])
</form>
@endsection
