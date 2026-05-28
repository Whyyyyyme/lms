@extends('layouts.app', ['title' => 'Laporan Aktivitas'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Laporan', 'title' => 'Aktivitas Sistem'])
<div class="space-y-3">@forelse($activities as $activity)<div class="rounded-3xl border bg-white p-5 shadow-sm"><div class="flex justify-between gap-4"><div><p class="font-semibold">{{ $activity->title ?? $activity->type }}</p><p class="text-sm text-slate-500">{{ $activity->message }}</p><p class="mt-1 text-xs text-slate-400">Untuk: {{ $activity->user?->name ?? '-' }}</p></div><span class="text-xs text-slate-400">{{ $activity->created_at?->format('d M Y H:i') }}</span></div></div>@empty @include('partials.empty-state') @endforelse</div><div class="mt-5">{{ $activities->links() }}</div>
@endsection
