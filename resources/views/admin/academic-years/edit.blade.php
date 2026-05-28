@extends('layouts.app', ['title' => 'Edit Tahun Akademik'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Edit Tahun Akademik'])
<form action="{{ route('admin.tahun-akademik.update', $academicYear) }}" method="POST" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    @csrf @method('PUT')
    @include('admin.academic-years._form')
    @include('partials.form.actions', ['cancel' => route('admin.tahun-akademik.index'), 'label' => 'Perbarui'])
</form>
@endsection
