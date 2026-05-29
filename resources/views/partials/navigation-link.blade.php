@php
    $href = $href ?? '#';
    $active = $active ?? false;
    $icon = $icon ?? '•';
    $label = $label ?? '';
    $badge = $badge ?? null;
    $method = strtoupper($method ?? 'GET');
@endphp

@if ($method === 'POST')
    <form method="POST" action="{{ $href }}">
        @csrf
        <button type="submit"
            class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ $active ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}">
            <span class="grid h-7 w-7 place-items-center rounded-lg {{ $active ? 'bg-white/15' : 'bg-slate-100 group-hover:bg-white' }}">{{ $icon }}</span>
            <span class="flex-1 text-left">{{ $label }}</span>
            @if($badge)
                <span class="rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-700">{{ $badge }}</span>
            @endif
        </button>
    </form>
@else
    <a href="{{ $href }}"
        class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ $active ? 'bg-indigo-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-slate-950' }}">
        <span class="grid h-7 w-7 place-items-center rounded-lg {{ $active ? 'bg-white/15' : 'bg-slate-100 group-hover:bg-white' }}">{{ $icon }}</span>
        <span class="flex-1">{{ $label }}</span>
        @if($badge)
            <span class="rounded-full bg-rose-100 px-2 py-0.5 text-xs font-semibold text-rose-700">{{ $badge }}</span>
        @endif
    </a>
@endif
