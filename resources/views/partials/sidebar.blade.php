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

    $roleLabel = $isAdmin ? 'Admin' : ($isAssistant ? 'Asisten Praktikum' : ($isStudent ? 'Mahasiswa' : 'Pengguna'));
    $isActive = fn (string $pattern): string => request()->is($pattern) ? 'active' : '';
@endphp

<div class="sidebar-overlay" data-sidebar-close></div>

<aside class="sidebar-drawer" aria-label="Sidebar menu">
    <div class="sidebar-header">
        <div>
            <div class="sidebar-title">LMS Praktikum</div>
            <div class="sidebar-subtitle">Menu navigasi utama</div>
        </div>
        <button type="button" class="icon-btn" data-sidebar-close aria-label="Tutup menu">✕</button>
    </div>

    @auth
        <div class="sidebar-user">
            <div class="sidebar-user-name">{{ $user->name }}</div>
            <div class="sidebar-user-email">{{ $user->email }}</div>
            @if($isStudent && $user->studySemester)
                <div class="sidebar-user-role">{{ $roleLabel }} • {{ $user->studySemester->name }}</div>
            @else
                <div class="sidebar-user-role">{{ $roleLabel }}</div>
            @endif
        </div>
    @endauth

    <nav class="sidebar-nav">
        @if ($isAdmin)
            <div class="sidebar-section">Menu Admin</div>

            @if(Route::has('admin.dashboard'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('admin/dashboard') }}" href="{{ route('admin.dashboard') }}"><span class="sidebar-icon">🏠</span>Dashboard</a>
            @endif
            @if(Route::has('admin.users.index'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('admin/users*') }}" href="{{ route('admin.users.index') }}"><span class="sidebar-icon">👥</span>Kelola User</a>
            @endif
            @if(Route::has('admin.semester.index'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('admin/semester*') }}" href="{{ route('admin.semester.index') }}"><span class="sidebar-icon">🎓</span>Semester Mahasiswa</a>
            @endif
            @if(Route::has('admin.tahun-akademik.index'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('admin/tahun-akademik*') }}" href="{{ route('admin.tahun-akademik.index') }}"><span class="sidebar-icon">📅</span>Tahun Akademik</a>
            @endif
            @if(Route::has('admin.matakuliah.index'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('admin/matakuliah*') }}" href="{{ route('admin.matakuliah.index') }}"><span class="sidebar-icon">📚</span>Matakuliah</a>
            @endif
            @if(Route::has('admin.kelas.index'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('admin/kelas*') }}" href="{{ route('admin.kelas.index') }}"><span class="sidebar-icon">🏫</span>Kelas Praktikum</a>
            @endif

            <div class="sidebar-section">Laporan</div>
            @if(Route::has('admin.reports.scores'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('admin/laporan/nilai*') }}" href="{{ route('admin.reports.scores') }}"><span class="sidebar-icon">📊</span>Laporan Nilai</a>
            @endif
            @if(Route::has('admin.reports.attendances'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('admin/laporan/absensi*') }}" href="{{ route('admin.reports.attendances') }}"><span class="sidebar-icon">✅</span>Laporan Absensi</a>
            @endif
            @if(Route::has('admin.reports.activities'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('admin/laporan/aktivitas*') }}" href="{{ route('admin.reports.activities') }}"><span class="sidebar-icon">🧾</span>Aktivitas</a>
            @endif
            @if(Route::has('admin.settings.edit'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('admin/pengaturan*') }}" href="{{ route('admin.settings.edit') }}"><span class="sidebar-icon">⚙️</span>Pengaturan</a>
            @endif
        @endif

        @if ($isAssistant)
            <div class="sidebar-section">Menu Asisten</div>

            @if(Route::has('assistant.dashboard'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('asisten/dashboard') }}" href="{{ route('assistant.dashboard') }}"><span class="sidebar-icon">🏠</span>Dashboard</a>
            @endif
            @if(Route::has('assistant.materi.index'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('asisten/materi*') }}" href="{{ route('assistant.materi.index') }}"><span class="sidebar-icon">📘</span>Materi</a>
            @endif
            @if(Route::has('assistant.tugas.index'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('asisten/tugas*') }}" href="{{ route('assistant.tugas.index') }}"><span class="sidebar-icon">📝</span>Tugas</a>
            @endif
            @if(Route::has('assistant.submissions.index'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('asisten/submissions*') }}" href="{{ route('assistant.submissions.index') }}"><span class="sidebar-icon">📥</span>Submission</a>
            @endif
            @if(Route::has('assistant.attendances.index'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('asisten/absensi*') }}" href="{{ route('assistant.attendances.index') }}"><span class="sidebar-icon">✅</span>Absensi</a>
            @endif
            @if(Route::has('assistant.pengumuman.index'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('asisten/pengumuman*') }}" href="{{ route('assistant.pengumuman.index') }}"><span class="sidebar-icon">📢</span>Pengumuman</a>
            @endif

            <div class="sidebar-section">Export</div>
            @if(Route::has('assistant.exports.scores.excel'))
                <a data-sidebar-link class="sidebar-link" href="{{ route('assistant.exports.scores.excel') }}"><span class="sidebar-icon">📗</span>Export Nilai</a>
            @endif
            @if(Route::has('assistant.exports.attendances.excel'))
                <a data-sidebar-link class="sidebar-link" href="{{ route('assistant.exports.attendances.excel') }}"><span class="sidebar-icon">📕</span>Export Absensi</a>
            @endif
        @endif

        @if ($isStudent)
            <div class="sidebar-section">Menu Mahasiswa</div>

            @if(Route::has('student.dashboard'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('mahasiswa/dashboard') }}" href="{{ route('student.dashboard') }}"><span class="sidebar-icon">🏠</span>Dashboard</a>
            @endif
            @if(Route::has('student.materials.index'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('mahasiswa/materi*') }}" href="{{ route('student.materials.index') }}"><span class="sidebar-icon">📘</span>Materi</a>
            @endif
            @if(Route::has('student.assignments.index'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('mahasiswa/tugas*') }}" href="{{ route('student.assignments.index') }}"><span class="sidebar-icon">📝</span>Tugas</a>
            @endif
            @if(Route::has('student.grades.index'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('mahasiswa/nilai*') }}" href="{{ route('student.grades.index') }}"><span class="sidebar-icon">⭐</span>Nilai</a>
            @endif
            @if(Route::has('student.attendances.index'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('mahasiswa/absensi*') }}" href="{{ route('student.attendances.index') }}"><span class="sidebar-icon">✅</span>Absensi</a>
            @endif
            @if(Route::has('student.calendar.index'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('mahasiswa/kalender*') }}" href="{{ route('student.calendar.index') }}"><span class="sidebar-icon">📅</span>Jadwal & Kalender</a>
            @endif
            @if(Route::has('student.chatbot.index'))
                <a data-sidebar-link class="sidebar-link {{ $isActive('mahasiswa/chatbot*') }}" href="{{ route('student.chatbot.index') }}"><span class="sidebar-icon">🤖</span>AI Chatbot</a>
            @endif
        @endif

        <div class="sidebar-section">Akun</div>

        @if(Route::has('notifications.index'))
            <a data-sidebar-link class="sidebar-link {{ $isActive('notifikasi*') }}" href="{{ route('notifications.index') }}"><span class="sidebar-icon">🔔</span>Notifikasi</a>
        @endif

        @if(Route::has('logout'))
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-link sidebar-logout"><span class="sidebar-icon">🚪</span>Logout</button>
            </form>
        @endif
    </nav>
</aside>
