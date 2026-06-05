@php
    use Illuminate\Support\Facades\Route;

    $user = auth()->user();

    $isAdmin = $user && method_exists($user, 'hasRole') && $user->hasRole('admin');
    $isAssistant = $user && method_exists($user, 'hasRole') && $user->hasRole('asisten');
    $isStudent = $user && method_exists($user, 'hasRole') && $user->hasRole('mahasiswa');

    if ($user && ! method_exists($user, 'hasRole')) {
        $isAdmin = ($user->role ?? null) === 'admin';
        $isAssistant = ($user->role ?? null) === 'asisten';
        $isStudent = ($user->role ?? null) === 'mahasiswa';
    }

    $roleLabel = $isAdmin
        ? 'Admin'
        : ($isAssistant
            ? 'Asisten Praktikum'
            : ($isStudent ? 'Mahasiswa' : 'Pengguna'));

    $activeClass = function (array|string $patterns): string {
        foreach ((array) $patterns as $pattern) {
            if (request()->is($pattern)) {
                return 'active';
            }
        }

        return '';
    };

    $studentSemesterName = null;

    if ($isStudent && $user) {
        $studentSemesterName = optional($user->studySemester ?? null)->name;
    }

    $icon = function (string $name): string {
        return match ($name) {
            'dashboard' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h7v7H4V4Z"/><path d="M13 4h7v7h-7V4Z"/><path d="M4 13h7v7H4v-7Z"/><path d="M13 13h7v7h-7v-7Z"/></svg>',
            'users' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M16 21v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
            'semester' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m22 10-10-6-10 6 10 6 10-6Z"/><path d="M6 12v5c0 1.1 2.7 3 6 3s6-1.9 6-3v-5"/></svg>',
            'calendar' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M8 2v4"/><path d="M16 2v4"/><path d="M3 10h18"/><path d="M5 4h14a2 2 0 0 1 2 2v13a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"/></svg>',
            'book' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z"/></svg>',
            'class' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 21h18"/><path d="M5 21V7l7-4 7 4v14"/><path d="M9 21v-7h6v7"/><path d="M9 9h.01"/><path d="M15 9h.01"/></svg>',
            'chart' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 3v18h18"/><path d="M7 16v-5"/><path d="M12 16V8"/><path d="M17 16v-3"/></svg>',
            'attendance' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>',
            'report' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/></svg>',
            'settings' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 15.5A3.5 3.5 0 1 0 12 8a3.5 3.5 0 0 0 0 7.5Z"/><path d="M19.4 15a1.7 1.7 0 0 0 .34 1.88l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06A1.7 1.7 0 0 0 15 19.4a1.7 1.7 0 0 0-1 .6 1.7 1.7 0 0 0-.4 1.1V21a2 2 0 1 1-4 0v-.09A1.7 1.7 0 0 0 8.6 19.4a1.7 1.7 0 0 0-1.88.34l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.7 1.7 0 0 0 4.6 15a1.7 1.7 0 0 0-.6-1 1.7 1.7 0 0 0-1.1-.4H3a2 2 0 1 1 0-4h.09A1.7 1.7 0 0 0 4.6 8.6a1.7 1.7 0 0 0-.34-1.88l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.7 1.7 0 0 0 9 4.6a1.7 1.7 0 0 0 1-.6 1.7 1.7 0 0 0 .4-1.1V3a2 2 0 1 1 4 0v.09A1.7 1.7 0 0 0 15.4 4.6a1.7 1.7 0 0 0 1.88-.34l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.7 1.7 0 0 0 19.4 9c.36.15.7.36 1 .6.3.25.65.4 1.1.4H21a2 2 0 1 1 0 4h-.09A1.7 1.7 0 0 0 19.4 15Z"/></svg>',
            'inbox' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v7a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-7l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11Z"/></svg>',
            'announcement' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 11v2a2 2 0 0 0 2 2h2l4 4v-4h3l7 3V6l-7 3H5a2 2 0 0 0-2 2Z"/><path d="M14 9v6"/></svg>',
            'export' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M12 18v-6"/><path d="m9 15 3 3 3-3"/></svg>',
            'star' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m12 2 3.09 6.26L22 9.27l-5 4.88 1.18 6.88L12 17.77l-6.18 3.26L7 14.15 2 9.27l6.91-1.01L12 2Z"/></svg>',
            'bot' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 8V4"/><path d="M8 4h8"/><rect x="4" y="8" width="16" height="12" rx="3"/><path d="M9 13h.01"/><path d="M15 13h.01"/><path d="M9 17h6"/></svg>',
            'bell' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>',
            'logout' => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>',
            default => '<svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9"/></svg>',
        };
    };
@endphp

<div class="sidebar-overlay" data-sidebar-close></div>

<aside class="sidebar-drawer" aria-label="Sidebar menu">
    <div class="sidebar-header">
        <div>
            <div class="sidebar-title">LMS Praktikum</div>
            <div class="sidebar-subtitle">
                Sistem pembelajaran praktikum berbasis mata kuliah.
            </div>
        </div>

        <button type="button" class="icon-btn" data-sidebar-close aria-label="Tutup menu">
            ✕
        </button>
    </div>

    @auth
        <div class="sidebar-user">
            <div class="sidebar-user-name">
                {{ $user->name }}
            </div>

            <div class="sidebar-user-email">
                {{ $user->email }}
            </div>

            <div class="sidebar-user-role">
                @if($isStudent && $studentSemesterName)
                    {{ $roleLabel }} • {{ $studentSemesterName }}
                @else
                    {{ $roleLabel }}
                @endif
            </div>
        </div>
    @endauth

    <nav class="sidebar-nav">
        @if ($isAdmin)
            <div class="sidebar-section">Dashboard</div>

            @if(Route::has('admin.dashboard'))
                <a data-sidebar-link class="sidebar-link {{ $activeClass('admin/dashboard') }}" href="{{ route('admin.dashboard') }}">
                    <span class="sidebar-icon">{!! $icon('dashboard') !!}</span>
                    <span>Dashboard Admin</span>
                </a>
            @endif

            <div class="sidebar-section">Data Master</div>

            @if(Route::has('admin.users.index'))
                <a data-sidebar-link class="sidebar-link {{ $activeClass('admin/users*') }}" href="{{ route('admin.users.index') }}">
                    <span class="sidebar-icon">{!! $icon('users') !!}</span>
                    <span>Asisten & Mahasiswa</span>
                </a>
            @endif

            @if(Route::has('admin.semester.index'))
                <a data-sidebar-link class="sidebar-link {{ $activeClass('admin/semester*') }}" href="{{ route('admin.semester.index') }}">
                    <span class="sidebar-icon">{!! $icon('semester') !!}</span>
                    <span>Semester Mahasiswa</span>
                </a>
            @endif

            @if(Route::has('admin.tahun-akademik.index'))
                <a data-sidebar-link class="sidebar-link {{ $activeClass('admin/tahun-akademik*') }}" href="{{ route('admin.tahun-akademik.index') }}">
                    <span class="sidebar-icon">{!! $icon('calendar') !!}</span>
                    <span>Tahun Akademik</span>
                </a>
            @endif

            <div class="sidebar-section">Akademik</div>

            @if(Route::has('admin.matakuliah.index'))
                <a data-sidebar-link class="sidebar-link {{ $activeClass('admin/matakuliah*') }}" href="{{ route('admin.matakuliah.index') }}">
                    <span class="sidebar-icon">{!! $icon('book') !!}</span>
                    <span>Mata Kuliah</span>
                </a>
            @endif

            @if(Route::has('admin.kelas.index'))
                <a data-sidebar-link class="sidebar-link {{ $activeClass('admin/kelas*') }}" href="{{ route('admin.kelas.index') }}">
                    <span class="sidebar-icon">{!! $icon('class') !!}</span>
                    <span>Kelas Praktikum</span>
                </a>
            @endif

            <div class="sidebar-section">Laporan</div>

            @if(Route::has('admin.reports.scores'))
                <a data-sidebar-link class="sidebar-link {{ $activeClass('admin/laporan/nilai*') }}" href="{{ route('admin.reports.scores') }}">
                    <span class="sidebar-icon">{!! $icon('chart') !!}</span>
                    <span>Laporan Nilai</span>
                </a>
            @endif

            @if(Route::has('admin.reports.attendances'))
                <a data-sidebar-link class="sidebar-link {{ $activeClass('admin/laporan/absensi*') }}" href="{{ route('admin.reports.attendances') }}">
                    <span class="sidebar-icon">{!! $icon('attendance') !!}</span>
                    <span>Laporan Absensi</span>
                </a>
            @endif

            @if(Route::has('admin.reports.activities'))
                <a data-sidebar-link class="sidebar-link {{ $activeClass('admin/laporan/aktivitas*') }}" href="{{ route('admin.reports.activities') }}">
                    <span class="sidebar-icon">{!! $icon('report') !!}</span>
                    <span>Aktivitas Sistem</span>
                </a>
            @endif

            <div class="sidebar-section">Sistem</div>

            @if(Route::has('admin.settings.edit'))
                <a data-sidebar-link class="sidebar-link {{ $activeClass('admin/pengaturan*') }}" href="{{ route('admin.settings.edit') }}">
                    <span class="sidebar-icon">{!! $icon('settings') !!}</span>
                    <span>Pengaturan Sistem</span>
                </a>
            @endif
        @endif

        @if ($isAssistant)
            <div class="sidebar-section">Menu Asisten</div>

            @if(Route::has('assistant.dashboard'))
                <a data-sidebar-link class="sidebar-link {{ $activeClass(['asisten/dashboard', 'asisten/mata-kuliah*']) }}" href="{{ route('assistant.dashboard') }}">
                    <span class="sidebar-icon">{!! $icon('book') !!}</span>
                    <span>Mata Kuliah</span>
                </a>
            @endif

            @if(Route::has('assistant.submissions.index'))
                <a data-sidebar-link class="sidebar-link {{ $activeClass('asisten/submissions*') }}" href="{{ route('assistant.submissions.index') }}">
                    <span class="sidebar-icon">{!! $icon('inbox') !!}</span>
                    <span>Submission</span>
                </a>
            @endif

            @if(Route::has('assistant.pengumuman.index'))
                <a data-sidebar-link class="sidebar-link {{ $activeClass('asisten/pengumuman*') }}" href="{{ route('assistant.pengumuman.index') }}">
                    <span class="sidebar-icon">{!! $icon('announcement') !!}</span>
                    <span>Pengumuman</span>
                </a>
            @endif

            <div class="sidebar-section">Export</div>

            @if(Route::has('assistant.exports.scores.excel'))
                <a data-sidebar-link class="sidebar-link" href="{{ route('assistant.exports.scores.excel') }}">
                    <span class="sidebar-icon">{!! $icon('export') !!}</span>
                    <span>Export Nilai</span>
                </a>
            @endif

            @if(Route::has('assistant.exports.attendances.excel'))
                <a data-sidebar-link class="sidebar-link" href="{{ route('assistant.exports.attendances.excel') }}">
                    <span class="sidebar-icon">{!! $icon('export') !!}</span>
                    <span>Export Absensi</span>
                </a>
            @endif
        @endif

        @if ($isStudent)
            <div class="sidebar-section">Menu Mahasiswa</div>

            @if(Route::has('student.dashboard'))
                <a data-sidebar-link class="sidebar-link {{ $activeClass('mahasiswa/dashboard') }}" href="{{ route('student.dashboard') }}">
                    <span class="sidebar-icon">{!! $icon('dashboard') !!}</span>
                    <span>Dashboard</span>
                </a>
            @endif

            @if(Route::has('student.courses.index'))
                <a data-sidebar-link class="sidebar-link {{ $activeClass('mahasiswa/mata-kuliah*') }}" href="{{ route('student.courses.index') }}">
                    <span class="sidebar-icon">{!! $icon('book') !!}</span>
                    <span>Mata Kuliah Saya</span>
                </a>
            @endif

            @if(Route::has('student.schedule.index'))
                <a data-sidebar-link class="sidebar-link {{ $activeClass('mahasiswa/jadwal*') }}" href="{{ route('student.schedule.index') }}">
                    <span class="sidebar-icon">{!! $icon('calendar') !!}</span>
                    <span>Jadwal Praktikum</span>
                </a>
            @endif

            @if(Route::has('student.grades.index'))
                <a data-sidebar-link class="sidebar-link {{ $activeClass('mahasiswa/nilai*') }}" href="{{ route('student.grades.index') }}">
                    <span class="sidebar-icon">{!! $icon('star') !!}</span>
                    <span>Nilai Saya</span>
                </a>
            @endif

            @if(Route::has('student.chatbot.index'))
                <a data-sidebar-link class="sidebar-link {{ $activeClass('mahasiswa/chatbot*') }}" href="{{ route('student.chatbot.index') }}">
                    <span class="sidebar-icon">{!! $icon('bot') !!}</span>
                    <span>AI Chatbot</span>
                </a>
            @endif
        @endif

        <div class="sidebar-section">Akun</div>

        @if(Route::has('notifications.index'))
            <a data-sidebar-link class="sidebar-link {{ $activeClass('notifikasi*') }}" href="{{ route('notifications.index') }}">
                <span class="sidebar-icon">{!! $icon('bell') !!}</span>
                <span>Notifikasi</span>
            </a>
        @endif

        @if(Route::has('logout'))
            <form method="POST" action="{{ route('logout') }}">
                @csrf

                <button type="submit" class="sidebar-link sidebar-logout">
                    <span class="sidebar-icon">{!! $icon('logout') !!}</span>
                    <span>Logout</span>
                </button>
            </form>
        @endif
    </nav>
</aside>