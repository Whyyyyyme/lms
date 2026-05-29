@php
    $title = $title ?? 'Fitur';
    $description = $description ?? 'Buka fitur ini.';
    $href = $href ?? '#';
    $icon = $icon ?? '➡️';
    $disabled = $disabled ?? false;
@endphp
<a href="{{ $disabled ? '#' : $href }}"
   class="group block rounded-3xl border border-slate-200 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:border-indigo-200 hover:shadow-md {{ $disabled ? 'pointer-events-none opacity-60' : '' }}">
    <div class="flex items-start gap-4">
        <div class="grid h-12 w-12 flex-none place-items-center rounded-2xl bg-slate-100 text-2xl transition group-hover:bg-indigo-50">{{ $icon }}</div>
        <div>
            <h3 class="font-bold text-slate-950">{{ $title }}</h3>
            <p class="mt-1 text-sm leading-6 text-slate-500">{{ $description }}</p>
            <p class="mt-3 text-sm font-bold text-indigo-600">Buka fitur →</p>
        </div>
    </div>
</a>
