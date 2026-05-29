@extends('layouts.app')

@section('title', 'Tambah Semester Mahasiswa')

@section('content')
    @include('partials.page-header', ['title' => 'Tambah Semester Mahasiswa', 'description' => 'Buat semester mahasiswa baru.'])
    <form method="POST" action="{{ route('admin.semester.store') }}" class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @include('admin.study-semesters._form')
        <div class="mt-6 flex gap-3">
            <button class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Simpan</button>
            <a class="rounded-xl border px-4 py-2 text-sm" href="{{ route('admin.semester.index') }}">Batal</a>
        </div>
    </form>
@endsection
