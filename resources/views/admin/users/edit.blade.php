@extends('layouts.app', ['title' => 'Edit User'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Edit User'])
<form action="{{ route('admin.users.update', $user) }}" method="POST" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    @csrf
    @method('PUT')
    @include('admin.users._form')
    @include('partials.form.actions', ['cancel' => route('admin.users.index'), 'label' => 'Perbarui User'])
</form>
@endsection
