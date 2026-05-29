@php
    use Illuminate\Support\Facades\Route;
@endphp
<header class="sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur">
    <div class="flex h-16 items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <button type="button" class="rounded-xl border border-slate-200 bg-white p-2 text-slate-600 shadow-sm lg:hidden" @click="sidebarOpen = true">
                <span class="sr-only">Buka menu</span>
                ☰
            </button>
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">{{ now()->translatedFormat('l, d F Y') }}</p>
                <h1 class="text-lg font-bold text-slate-950">@yield('page_title', 'Dashboard')</h1>
            </div>
        </div>

        <div class="flex items-center gap-3">
            @if(class_exists(\App\Livewire\NotificationDropdown::class))
                <livewire:notification-dropdown />
            @elseif(Route::has('notifications.index'))
                <a href="{{ route('notifications.index') }}" class="rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">🔔 Notifikasi</a>
            @endif

            <div class="hidden items-center gap-3 rounded-2xl border border-slate-200 bg-white px-3 py-2 shadow-sm sm:flex">
                <div class="grid h-8 w-8 place-items-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                </div>
                <div class="leading-tight">
                    <p class="text-sm font-semibold text-slate-900">{{ auth()->user()?->name ?? 'Pengguna' }}</p>
                    <p class="text-xs text-slate-500">{{ auth()->user()?->nim_nip ?? auth()->user()?->email }}</p>
                </div>
            </div>
        </div>
    </div>
</header>
