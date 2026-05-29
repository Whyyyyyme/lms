@extends('layouts.app')
@section('title', 'Tambah Tahun Akademik')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Tambah Tahun Akademik'])
<form action="{{ route('admin.tahun-akademik.store') }}" method="POST" class="form-card">@csrf @include('admin.academic-years._form') @include('partials.form.actions', ['cancel' => route('admin.tahun-akademik.index'), 'label' => 'Simpan Tahun Akademik'])</form>
@endsection
