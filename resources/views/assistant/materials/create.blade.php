@extends('layouts.app', ['title' => 'Upload Materi'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Upload Materi'])
<form action="{{ route('assistant.materi.store') }}" method="POST" enctype="multipart/form-data" class="rounded-3xl border bg-white p-6 shadow-sm">@csrf @include('assistant.materials._form') @include('partials.form.actions', ['cancel' => route('assistant.materi.index'), 'label' => 'Simpan Materi'])</form>
@endsection
