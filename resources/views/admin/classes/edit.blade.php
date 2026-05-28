@extends('layouts.app', ['title' => 'Edit Kelas'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Edit Kelas Praktikum'])
<form action="{{ route('admin.kelas.update', $praktikumClass) }}" method="POST" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">@csrf @method('PUT') @include('admin.classes._form') @include('partials.form.actions', ['cancel' => route('admin.kelas.index'), 'label' => 'Perbarui'])</form>
@endsection
