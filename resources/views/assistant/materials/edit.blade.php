@extends('layouts.app', ['title' => 'Edit Materi'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Edit Materi'])
<form action="{{ route('assistant.materi.update', $material) }}" method="POST" enctype="multipart/form-data" class="rounded-3xl border bg-white p-6 shadow-sm">@csrf @method('PUT') @include('assistant.materials._form') @include('partials.form.actions', ['cancel' => route('assistant.materi.index'), 'label' => 'Perbarui Materi'])</form>
@endsection
