@extends('layouts.app')
@section('title', 'Edit Matakuliah')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Edit Matakuliah'])
<form action="{{ route('admin.matakuliah.update', $course) }}" method="POST" class="form-card">@csrf @method('PUT') @include('admin.courses._form') @include('partials.form.actions', ['cancel' => route('admin.matakuliah.index'), 'label' => 'Update Matakuliah'])</form>
@endsection
