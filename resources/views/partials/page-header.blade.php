@php
    $title = $title ?? 'Halaman';
    $description = $description ?? null;
    $actionLabel = $actionLabel ?? null;
    $actionUrl = $actionUrl ?? null;
@endphp
<div class="flex flex-col gap-4 rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:flex-row sm:items-center sm:justify-between">
    <div>
        <h2 class="text-2xl font-black tracking-tight text-slate-950">{{ $title }}</h2>
        @if($description)
            <p class="mt-1 max-w-2xl text-sm leading-6 text-slate-500">{{ $description }}</p>
        @endif
    </div>

    @if($actionLabel && $actionUrl)
        <a href="{{ $actionUrl }}" class="inline-flex items-center justify-center rounded-2xl bg-indigo-600 px-4 py-2.5 text-sm font-bold text-white shadow-sm hover:bg-indigo-700">
            {{ $actionLabel }}
        </a>
    @endif
</div>
