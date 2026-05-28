@extends('layouts.app', ['title' => 'Tambah Kelas'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Tambah Kelas Praktikum'])
<form action="{{ route('admin.kelas.store') }}" method="POST" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">@csrf @include('admin.classes._form') @include('partials.form.actions', ['cancel' => route('admin.kelas.index')])</form>
@endsection
