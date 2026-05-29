@php
    $title = $title ?? 'Belum ada data';
    $description = $description ?? 'Data akan tampil di sini setelah tersedia.';
    $icon = $icon ?? '📭';
@endphp
<div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center">
    <div class="mx-auto grid h-14 w-14 place-items-center rounded-2xl bg-slate-100 text-3xl">{{ $icon }}</div>
    <h3 class="mt-4 text-base font-bold text-slate-950">{{ $title }}</h3>
    <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
</div>
