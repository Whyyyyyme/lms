@extends('layouts.app')

@section('title', 'AI Chatbot')

@section('content')
    <div class="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-slate-900">AI Chatbot</h1>
            <p class="mt-1 text-sm text-slate-600">
                Chatbot ini membaca konteks kelas, materi, tugas, status submission, nilai, dan feedback kamu AI.
            </p>
        </div>

        <livewire:chatbot-widget />
    </div>
@endsection
