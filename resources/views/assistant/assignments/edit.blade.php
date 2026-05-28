@extends('layouts.app', ['title' => 'Edit Tugas'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Edit Tugas'])
<form action="{{ route('assistant.tugas.update', $assignment) }}" method="POST" enctype="multipart/form-data" class="rounded-3xl border bg-white p-6 shadow-sm">@csrf @method('PUT') @include('assistant.assignments._form') @include('partials.form.actions', ['cancel' => route('assistant.tugas.index'), 'label' => 'Perbarui Tugas'])</form>
@endsection
