@extends('layouts.app', ['title' => 'Detail Materi'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => $material->title, 'description' => $material->kelas?->course?->name.' - '.$material->kelas?->name])
<section class="rounded-3xl border bg-white p-6 shadow-sm"><div class="mb-5 flex justify-end gap-3"><a href="{{ route('assistant.materi.edit', $material) }}" class="rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Edit</a>@include('partials.delete-button', ['action' => route('assistant.materi.destroy', $material)])</div><p class="whitespace-pre-line text-slate-700">{{ $material->description }}</p>@if($material->file_path)<a href="{{ str_starts_with($material->file_path, 'http') ? $material->file_path : asset('storage/'.$material->file_path) }}" target="_blank" class="mt-5 inline-flex rounded-2xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white">Buka File / Link</a>@endif</section>
@endsection
