@php
    $label = $label ?? 'Statistik';
    $value = $value ?? 0;
    $icon = $icon ?? '📌';
    $hint = $hint ?? null;
@endphp
<div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-sm font-semibold text-slate-500">{{ $label }}</p>
            <p class="mt-2 text-3xl font-black tracking-tight text-slate-950">{{ $value }}</p>
            @if($hint)
                <p class="mt-1 text-xs text-slate-400">{{ $hint }}</p>
            @endif
        </div>
        <div class="grid h-12 w-12 place-items-center rounded-2xl bg-indigo-50 text-2xl">{{ $icon }}</div>
    </div>
</div>
