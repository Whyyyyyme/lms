@extends('layouts.app', ['title' => 'AI Chatbot'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Mahasiswa', 'title' => 'AI Chatbot Praktikum', 'description' => 'Tanyakan materi, deadline tugas, atau minta penjelasan konsep praktikum.'])
@if (class_exists(\Livewire\Livewire::class))
    <livewire:chatbot-widget />
@else
    <section class="rounded-3xl border bg-white p-6 shadow-sm"><div class="space-y-3">@foreach($histories as $history)<div class="rounded-2xl {{ $history->role === 'user' ? 'bg-indigo-50' : 'bg-slate-50' }} p-4"><p class="text-xs font-semibold uppercase text-slate-500">{{ $history->role }}</p><p class="mt-1 text-sm text-slate-700">{{ $history->message }}</p></div>@endforeach</div><form action="{{ route('student.chatbot.send') }}" method="POST" class="mt-5 flex gap-3">@csrf<input name="message" class="flex-1 rounded-2xl border-slate-300" placeholder="Tulis pertanyaan..."><button class="rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Kirim</button></form></section>
@endif
@endsection
