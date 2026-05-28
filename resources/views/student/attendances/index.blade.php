@extends('layouts.app', ['title' => 'Absensi Saya'])
@section('content')
@include('partials.page-header', ['eyebrow' => 'Mahasiswa', 'title' => 'Absensi Saya'])
@if (class_exists(\Livewire\Livewire::class))<div class="mb-6"><livewire:absensi-live /></div>@endif
<div class="space-y-4">@forelse($attendances as $attendance)@php($record = $attendance->records->first())<article class="rounded-3xl border bg-white p-5 shadow-sm"><div class="flex flex-col justify-between gap-3 md:flex-row"><div><h2 class="font-bold">{{ $attendance->kelas?->course?->name }} - {{ $attendance->kelas?->name }}</h2><p class="text-sm text-slate-500">{{ $attendance->session_date?->format('d M Y') }}</p></div><div class="flex items-center gap-3">@include('partials.badge', ['slot' => $record?->status ?? 'alpha'])@if($attendance->is_open)<form action="{{ route('student.attendances.check-in', $attendance) }}" method="POST">@csrf<button class="rounded-2xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white">Check-in</button></form>@endif</div></div></article>@empty @include('partials.empty-state', ['title' => 'Belum ada absensi']) @endforelse</div><div class="mt-5">{{ $attendances->links() }}</div>
@endsection
