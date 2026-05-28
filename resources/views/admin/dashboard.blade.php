@extends('layouts.app', ['title' => 'Dashboard Admin'])

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Dashboard Admin',
    'description' => 'Ringkasan data utama LMS Praktikum.',
])

<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
    @foreach ($statistics as $label => $value)
        @include('partials.stat-card', ['label' => ucwords(str_replace('_', ' ', $label)), 'value' => $value])
    @endforeach
</div>

<div class="mt-8 grid gap-6 lg:grid-cols-2">
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-950">User Terbaru</h2>
        <div class="mt-4 space-y-3">
            @forelse ($latestUsers as $user)
                <div class="flex items-center justify-between rounded-2xl bg-slate-50 p-4">
                    <div>
                        <p class="font-semibold text-slate-900">{{ $user->name }}</p>
                        <p class="text-sm text-slate-500">{{ $user->email }}</p>
                    </div>
                    <span class="text-xs font-semibold text-slate-500">{{ $user->created_at?->diffForHumans() }}</span>
                </div>
            @empty
                @include('partials.empty-state', ['title' => 'Belum ada user'])
            @endforelse
        </div>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-950">Submission Terbaru</h2>
        <div class="mt-4 space-y-3">
            @forelse ($latestSubmissions as $submission)
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="font-semibold text-slate-900">{{ $submission->student?->name }}</p>
                    <p class="text-sm text-slate-500">{{ $submission->assignment?->title }} · {{ $submission->assignment?->kelas?->course?->name }}</p>
                </div>
            @empty
                @include('partials.empty-state', ['title' => 'Belum ada submission'])
            @endforelse
        </div>
    </section>
</div>
@endsection
