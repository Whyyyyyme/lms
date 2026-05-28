@extends('layouts.app', ['title' => 'Detail Materi'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Mahasiswa', 'title' => $material->title, 'description' => $material->kelas?->course?->name.' - '.$material->kelas?->name])
<section class="rounded-3xl border bg-white p-6 shadow-sm"><p class="whitespace-pre-line text-slate-700">{{ $material->description }}</p>@if($material->file_path)<a href="{{ route('student.materials.download', $material) }}" class="mt-6 inline-flex rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Download / Buka Materi</a>@endif</section>
@endsection
