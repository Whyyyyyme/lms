@extends('layouts.app')
@section('title', 'Edit Kelas Praktikum')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Edit Kelas Praktikum'])
<form action="{{ route('admin.kelas.update', $praktikumClass) }}" method="POST" class="form-card">@csrf @method('PUT') @include('admin.classes._form') @include('partials.form.actions', ['cancel' => route('admin.kelas.index'), 'label' => 'Update Kelas'])</form>
@endsection
