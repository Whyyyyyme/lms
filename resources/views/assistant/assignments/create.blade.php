@extends('layouts.app', ['title' => 'Buat Tugas'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Buat Tugas'])
<form action="{{ route('assistant.tugas.store') }}" method="POST" enctype="multipart/form-data" class="rounded-3xl border bg-white p-6 shadow-sm">@csrf @include('assistant.assignments._form') @include('partials.form.actions', ['cancel' => route('assistant.tugas.index'), 'label' => 'Simpan Tugas'])</form>
@endsection
