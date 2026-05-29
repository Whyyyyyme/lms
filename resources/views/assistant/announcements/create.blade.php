@extends('layouts.app')
@section('title', 'Buat Pengumuman')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Buat Pengumuman', 'description' => 'Kirim pengumuman ke mahasiswa pada kelas tertentu.'])
@if(($classes ?? collect())->isEmpty())<div class="alert alert-error">Kamu belum ditugaskan ke kelas mana pun. Minta admin mengatur kelas praktikum terlebih dahulu.</div>@endif
<form action="{{ route('assistant.pengumuman.store') }}" method="POST" class="form-card">
    @csrf
    @include('assistant.announcements._form')
    @include('partials.form.actions', ['cancel' => route('assistant.pengumuman.index'), 'label' => 'Kirim Pengumuman'])
</form>
@endsection
