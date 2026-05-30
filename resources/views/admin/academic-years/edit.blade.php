@extends('layouts.app')

@section('title', 'Edit Tahun Akademik')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Edit Tahun Akademik',
    'description' => 'Perbarui periode tahun akademik.'
])

<form action="{{ route('admin.tahun-akademik.update', $academicYear) }}" method="POST" class="form-card">
    @csrf
    @method('PUT')

    @include('admin.academic-years._form')

    @include('partials.form.actions', [
        'cancel' => route('admin.tahun-akademik.show', $academicYear),
        'label' => 'Update Tahun Akademik'
    ])
</form>
@endsection