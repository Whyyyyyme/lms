@extends('layouts.app', ['title' => 'Edit Matakuliah'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Edit Matakuliah'])
<form action="{{ route('admin.matakuliah.update', $course) }}" method="POST" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">@csrf @method('PUT') @include('admin.courses._form') @include('partials.form.actions', ['cancel' => route('admin.matakuliah.index'), 'label' => 'Perbarui'])</form>
@endsection
