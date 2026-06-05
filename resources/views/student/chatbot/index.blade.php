@extends('layouts.app')

@section('title', 'AI Chatbot')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $dashboardUrl = Route::has('student.dashboard')
        ? route('student.dashboard')
        : '#';

    $coursesUrl = Route::has('student.courses.index')
        ? route('student.courses.index')
        : '#';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Mahasiswa</div>

    <h1>AI Chatbot</h1>

    <p>
        Gunakan chatbot untuk membantu memahami materi praktikum, mengecek tugas,
        melihat status submission, nilai, feedback, dan konteks kelas yang kamu ikuti.
    </p>

    <div class="hero-actions">
        <a href="{{ $dashboardUrl }}" class="btn">
            ← Dashboard
        </a>

        @if(Route::has('student.courses.index'))
            <a href="{{ $coursesUrl }}" class="btn btn-primary">
                📚 Mata Kuliah Saya
            </a>
        @endif
    </div>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Asisten Belajar AI</h2>
            <div class="section-subtitle">
                Chatbot membaca konteks kelas, materi, tugas, submission, nilai, dan feedback kamu.
            </div>
        </div>

        <span class="status-pill status-info">
            AI Chatbot
        </span>
    </div>

    <div
        style="
            border: 1px solid var(--line);
            border-radius: 22px;
            background: #f8fafc;
            overflow: hidden;
        "
    >
        <livewire:chatbot-widget />
    </div>
</section>
@endsection