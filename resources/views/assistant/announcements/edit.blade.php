@extends('layouts.app')
@section('title', 'Edit Pengumuman')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Edit Pengumuman'])
<form action="{{ route('assistant.pengumuman.update', $announcement) }}" method="POST" class="form-card">
    @csrf
    @method('PUT')
    @include('assistant.announcements._form')
    @include('partials.form.actions', ['cancel' => route('assistant.pengumuman.index'), 'label' => 'Update Pengumuman'])
</form>
@endsection
