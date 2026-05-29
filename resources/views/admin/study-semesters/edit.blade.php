@extends('layouts.app')

@section('title', 'Edit Semester Mahasiswa')

@section('content')
    @include('partials.page-header', ['title' => 'Edit Semester Mahasiswa', 'description' => 'Perbarui data semester mahasiswa.'])
    <form method="POST" action="{{ route('admin.semester.update', $studySemester) }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('admin.study-semesters._form')
        <div class="mt-6 flex gap-3">
            <button class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Simpan</button>
            <a class="rounded-xl border px-4 py-2 text-sm" href="{{ route('admin.semester.index') }}">Batal</a>
        </div>
    </form>
@endsection
