@php
    $type = $type ?? 'default';
    $text = $text ?? '';
    $classes = match($type) {
        'success', 'aktif', 'hadir', 'sudah' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'warning', 'pending', 'izin' => 'bg-amber-50 text-amber-700 border-amber-200',
        'danger', 'alpha', 'belum' => 'bg-rose-50 text-rose-700 border-rose-200',
        'info' => 'bg-sky-50 text-sky-700 border-sky-200',
        default => 'bg-slate-50 text-slate-700 border-slate-200',
    };
@endphp
<span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-bold {{ $classes }}">{{ $text }}</span>
