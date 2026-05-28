@extends('layouts.app', ['title' => 'Jadwal Praktikum'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Mahasiswa', 'title' => 'Jadwal Praktikum'])
<div class="grid gap-4 md:grid-cols-2">@forelse($classes as $class)<div class="rounded-3xl border bg-white p-5 shadow-sm"><h2 class="font-bold">{{ $class->course?->name }} - {{ $class->name }}</h2><p class="mt-2 text-sm text-slate-600">{{ $class->schedule ?? 'Jadwal belum diatur' }}</p><p class="text-sm text-slate-500">Ruangan: {{ $class->room ?? '-' }}</p><p class="text-sm text-slate-500">Asisten: {{ $class->assistant?->name ?? '-' }}</p></div>@empty @include('partials.empty-state') @endforelse</div>
@endsection
