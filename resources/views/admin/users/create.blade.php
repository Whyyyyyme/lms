@extends('layouts.app', ['title' => 'Tambah User'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Tambah User'])
<form action="{{ route('admin.users.store') }}" method="POST" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    @csrf
    @include('admin.users._form')
    @include('partials.form.actions', ['cancel' => route('admin.users.index'), 'label' => 'Simpan User'])
</form>
@endsection
