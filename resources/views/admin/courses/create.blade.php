@extends('layouts.app', ['title' => 'Tambah Matakuliah'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Tambah Matakuliah'])
<form action="{{ route('admin.matakuliah.store') }}" method="POST" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">@csrf @include('admin.courses._form') @include('partials.form.actions', ['cancel' => route('admin.matakuliah.index')])</form>
@endsection
