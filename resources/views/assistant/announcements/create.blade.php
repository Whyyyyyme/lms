@extends('layouts.app', ['title' => 'Buat Pengumuman'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Buat Pengumuman'])
<form action="{{ route('assistant.pengumuman.store') }}" method="POST" class="rounded-3xl border bg-white p-6 shadow-sm">@csrf @include('assistant.announcements._form') @include('partials.form.actions', ['cancel' => route('assistant.pengumuman.index'), 'label' => 'Kirim Pengumuman'])</form>
@endsection
