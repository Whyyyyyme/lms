@php
    $map = [
        'admin' => 'bg-purple-100 text-purple-700',
        'asisten' => 'bg-sky-100 text-sky-700',
        'mahasiswa' => 'bg-emerald-100 text-emerald-700',
        'aktif' => 'bg-emerald-100 text-emerald-700',
        'nonaktif' => 'bg-slate-100 text-slate-700',
        'hadir' => 'bg-emerald-100 text-emerald-700',
        'izin' => 'bg-amber-100 text-amber-700',
        'alpha' => 'bg-red-100 text-red-700',
    ];
    $class = $map[strtolower((string) $slot)] ?? 'bg-slate-100 text-slate-700';
@endphp
<span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $class }}">{{ $slot }}</span>
