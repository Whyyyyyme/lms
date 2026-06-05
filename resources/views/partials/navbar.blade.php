@php
    $user = auth()->user();
    $roleLabel = 'Pengguna';

    if ($user) {
        if (method_exists($user, 'hasRole')) {
            $roleLabel = $user->hasRole('admin')
                ? 'Admin'
                : ($user->hasRole('asisten')
                    ? 'Asisten Praktikum'
                    : ($user->hasRole('mahasiswa') ? 'Mahasiswa' : 'Pengguna'));
        } elseif (! empty($user->role)) {
            $roleLabel = ucfirst($user->role);
        }
    }

    $initial = strtoupper(substr($user->name ?? 'U', 0, 1));
@endphp

<header class="topbar">
    <div class="topbar-left">
        <button type="button" class="hamburger-btn" data-sidebar-open aria-label="Buka menu">
            <span class="hamburger-lines" aria-hidden="true">
                <span></span>
                <span></span>
                <span></span>
            </span>
        </button>

        <div>
            <div class="brand-title">LMS Praktikum</div>
            <div class="brand-subtitle">
                Sistem Manajemen Pembelajaran Praktikum
            </div>
        </div>
    </div>

    <div class="topbar-right">
        @if (class_exists(\App\Livewire\NotificationDropdown::class))
            <livewire:notification-dropdown />
        @elseif (\Illuminate\Support\Facades\Route::has('notifications.index'))
            <a href="{{ route('notifications.index') }}" class="icon-btn" title="Notifikasi" aria-label="Notifikasi">
                🔔
            </a>
        @endif

        @auth
            <div class="user-chip" title="{{ $user->name }} - {{ $roleLabel }}">
                <span class="user-avatar">
                    {{ $initial }}
                </span>

                <span>
                    <span class="user-name">
                        {{ $user->name }}
                    </span>

                    <span class="user-role">
                        {{ $roleLabel }}
                    </span>
                </span>
            </div>
        @endauth
    </div>
</header>