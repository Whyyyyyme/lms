@extends('layouts.app')
@section('title', 'Nilai Submission')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Nilai Submission'])
<div class="form-card">
    <p><strong>Mahasiswa:</strong> {{ $submission->student?->name }}</p>
    <p><strong>Tugas:</strong> {{ $submission->assignment?->title }}</p>
    <p><strong>Dikumpulkan:</strong> {{ optional($submission->submitted_at)->format('d M Y H:i') }}</p>
    @if($submission->file_path)<p><a style="color:var(--primary);font-weight:700;" target="_blank" href="{{ asset('storage/'.$submission->file_path) }}">Download submission</a></p>@endif
    <form action="{{ route('assistant.submissions.grade', $submission) }}" method="POST" style="margin-top:18px;">
        @csrf
        @method('PATCH')
        <div class="form-grid">
            @include('partials.form.input', ['label' => 'Nilai', 'name' => 'score', 'type' => 'number', 'value' => $submission->score, 'required' => true])
            <div></div>
            <div style="grid-column:1/-1;">@include('partials.form.textarea', ['label' => 'Feedback', 'name' => 'feedback', 'value' => $submission->feedback])</div>
        </div>
        @include('partials.form.actions', ['cancel' => route('assistant.submissions.index'), 'label' => 'Simpan Nilai'])
    </form>
</div>
@endsection
