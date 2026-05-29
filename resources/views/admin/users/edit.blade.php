@extends('layouts.app')
@section('title', 'Edit User')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Edit User', 'description' => 'Perbarui data akun dan role user.'])
<form action="{{ route('admin.users.update', $user) }}" method="POST" class="form-card">
    @csrf
    @method('PUT')
    @include('admin.users._form')
    @include('partials.form.actions', ['cancel' => route('admin.users.index'), 'label' => 'Update User'])
</form>
@endsection
