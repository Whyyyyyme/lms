@extends('layouts.app', ['title' => 'Tambah Tahun Akademik'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Tambah Tahun Akademik'])
<form action="{{ route('admin.tahun-akademik.store') }}" method="POST" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    @csrf
    @include('admin.academic-years._form')
    @include('partials.form.actions', ['cancel' => route('admin.tahun-akademik.index')])
</form>
@endsection
