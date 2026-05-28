@extends('layouts.app', ['title' => 'Detail Pengumuman'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => $announcement->title, 'description' => $announcement->kelas?->course?->name.' - '.$announcement->kelas?->name])
<section class="rounded-3xl border bg-white p-6 shadow-sm"><div class="mb-5 flex justify-end gap-3"><a href="{{ route('assistant.pengumuman.edit', $announcement) }}" class="rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Edit</a>@include('partials.delete-button', ['action' => route('assistant.pengumuman.destroy', $announcement)])</div><p class="whitespace-pre-line text-slate-700">{{ $announcement->content }}</p></section>
@endsection
