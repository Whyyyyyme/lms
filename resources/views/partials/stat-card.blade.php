<div class="rounded-3xl border border-slate-200 bg-white p-5 shadow-sm">
    <p class="text-sm font-medium text-slate-500">{{ $label }}</p>
    <p class="mt-2 text-3xl font-bold text-slate-950">{{ $value }}</p>
    @isset($hint)
        <p class="mt-2 text-xs text-slate-500">{{ $hint }}</p>
    @endisset
</div>
