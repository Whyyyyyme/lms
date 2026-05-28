@extends('layouts.app', ['title' => 'Edit Pengumuman'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Edit Pengumuman'])
<form action="{{ route('assistant.pengumuman.update', $announcement) }}" method="POST" class="rounded-3xl border bg-white p-6 shadow-sm">@csrf @method('PUT') @include('assistant.announcements._form') @include('partials.form.actions', ['cancel' => route('assistant.pengumuman.index'), 'label' => 'Perbarui'])</form>
@endsection
