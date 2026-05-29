@php
    use Illuminate\Support\Facades\Route;

    $user = auth()->user();
    $safeRoute = function (string $name, array $params = [], string $fallback = '#') {
        return Route::has($name) ? route($name, $params) : $fallback;
    };
    $safeActive = function (string $pattern) {
        return request()->routeIs($pattern);
    };

    $isAdmin = $user?->hasRole('admin') ?? false;
    $isAssistant = $user?->hasRole('asisten') ?? false;
    $isStudent = $user?->hasRole('mahasiswa') ?? false;
@endphp

<div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-slate-950/40 lg:hidden" @click="sidebarOpen = false"></div>

<aside
    class="fixed inset-y-0 left-0 z-50 w-72 transform border-r border-slate-200 bg-white shadow-xl transition lg:translate-x-0 lg:shadow-none"
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
    <div class="flex h-full flex-col">
        <div class="flex h-16 items-center gap-3 border-b border-slate-200 px-5">
            <div class="grid h-10 w-10 place-items-center rounded-2xl bg-indigo-600 text-lg font-black text-white">L</div>
            <div>
                <div class="text-sm font-bold uppercase tracking-wide text-indigo-600">LMS Praktikum</div>
                <div class="text-xs text-slate-500">Sistem Pembelajaran</div>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-5">
            <div class="mb-5 rounded-2xl bg-slate-50 p-4">
                <p class="text-sm font-semibold text-slate-900">{{ $user?->name ?? 'Pengguna' }}</p>
                <p class="truncate text-xs text-slate-500">{{ $user?->email ?? '-' }}</p>
                <div class="mt-3 inline-flex rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
                    {{ $isAdmin ? 'Admin' : ($isAssistant ? 'Asisten Praktikum' : ($isStudent ? 'Mahasiswa' : 'User')) }}
                </div>
            </div>

            <nav class="space-y-7">
                @if($isAdmin)
                    <div>
                        <p class="mb-2 px-3 text-xs font-bold uppercase tracking-wider text-slate-400">Menu Admin</p>
                        <div class="space-y-1">
                            @include('partials.navigation-link', ['href' => $safeRoute('admin.dashboard'), 'active' => $safeActive('admin.dashboard'), 'icon' => '📊', 'label' => 'Dashboard'])
                            @include('partials.navigation-link', ['href' => $safeRoute('admin.users.index'), 'active' => $safeActive('admin.users.*'), 'icon' => '👥', 'label' => 'Kelola User'])
                            @include('partials.navigation-link', ['href' => $safeRoute('admin.tahun-akademik.index'), 'active' => $safeActive('admin.tahun-akademik.*'), 'icon' => '📅', 'label' => 'Tahun Akademik'])
                            @include('partials.navigation-link', ['href' => $safeRoute('admin.matakuliah.index'), 'active' => $safeActive('admin.matakuliah.*'), 'icon' => '📚', 'label' => 'Matakuliah'])
                            @include('partials.navigation-link', ['href' => $safeRoute('admin.kelas.index'), 'active' => $safeActive('admin.kelas.*'), 'icon' => '🏫', 'label' => 'Kelas Praktikum'])
                        </div>
                    </div>

                    <div>
                        <p class="mb-2 px-3 text-xs font-bold uppercase tracking-wider text-slate-400">Laporan</p>
                        <div class="space-y-1">
                            @include('partials.navigation-link', ['href' => $safeRoute('admin.reports.scores'), 'active' => $safeActive('admin.reports.scores'), 'icon' => '🧾', 'label' => 'Laporan Nilai'])
                            @include('partials.navigation-link', ['href' => $safeRoute('admin.reports.attendances'), 'active' => $safeActive('admin.reports.attendances'), 'icon' => '✅', 'label' => 'Laporan Absensi'])
                            @include('partials.navigation-link', ['href' => $safeRoute('admin.reports.activities'), 'active' => $safeActive('admin.reports.activities'), 'icon' => '📌', 'label' => 'Aktivitas'])
                            @include('partials.navigation-link', ['href' => $safeRoute('admin.settings.edit'), 'active' => $safeActive('admin.settings.*'), 'icon' => '⚙️', 'label' => 'Pengaturan'])
                        </div>
                    </div>
                @endif

                @if($isAssistant)
                    <div>
                        <p class="mb-2 px-3 text-xs font-bold uppercase tracking-wider text-slate-400">Menu Asisten</p>
                        <div class="space-y-1">
                            @include('partials.navigation-link', ['href' => $safeRoute('assistant.dashboard'), 'active' => $safeActive('assistant.dashboard'), 'icon' => '📊', 'label' => 'Dashboard'])
                            @include('partials.navigation-link', ['href' => $safeRoute('assistant.materi.index'), 'active' => $safeActive('assistant.materi.*'), 'icon' => '📘', 'label' => 'Materi'])
                            @include('partials.navigation-link', ['href' => $safeRoute('assistant.tugas.index'), 'active' => $safeActive('assistant.tugas.*'), 'icon' => '📝', 'label' => 'Tugas'])
                            @include('partials.navigation-link', ['href' => $safeRoute('assistant.submissions.index'), 'active' => $safeActive('assistant.submissions.*'), 'icon' => '📥', 'label' => 'Submission'])
                            @include('partials.navigation-link', ['href' => $safeRoute('assistant.attendances.index'), 'active' => $safeActive('assistant.attendances.*'), 'icon' => '✅', 'label' => 'Absensi'])
                            @include('partials.navigation-link', ['href' => $safeRoute('assistant.pengumuman.index'), 'active' => $safeActive('assistant.pengumuman.*'), 'icon' => '📢', 'label' => 'Pengumuman'])
                        </div>
                    </div>

                    <div>
                        <p class="mb-2 px-3 text-xs font-bold uppercase tracking-wider text-slate-400">Export</p>
                        <div class="space-y-1">
                            @include('partials.navigation-link', ['href' => $safeRoute('assistant.exports.scores.excel'), 'active' => false, 'icon' => '📗', 'label' => 'Export Nilai Excel'])
                            @include('partials.navigation-link', ['href' => $safeRoute('assistant.exports.attendances.excel'), 'active' => false, 'icon' => '📙', 'label' => 'Export Absensi Excel'])
                        </div>
                    </div>
                @endif

                @if($isStudent)
                    <div>
                        <p class="mb-2 px-3 text-xs font-bold uppercase tracking-wider text-slate-400">Menu Mahasiswa</p>
                        <div class="space-y-1">
                            @include('partials.navigation-link', ['href' => $safeRoute('student.dashboard'), 'active' => $safeActive('student.dashboard'), 'icon' => '📊', 'label' => 'Dashboard'])
                            @include('partials.navigation-link', ['href' => $safeRoute('student.materials.index'), 'active' => $safeActive('student.materials.*'), 'icon' => '📘', 'label' => 'Materi'])
                            @include('partials.navigation-link', ['href' => $safeRoute('student.assignments.index'), 'active' => $safeActive('student.assignments.*'), 'icon' => '📝', 'label' => 'Tugas'])
                            @include('partials.navigation-link', ['href' => $safeRoute('student.grades.index'), 'active' => $safeActive('student.grades.*'), 'icon' => '🏆', 'label' => 'Nilai'])
                            @include('partials.navigation-link', ['href' => $safeRoute('student.attendances.index'), 'active' => $safeActive('student.attendances.*'), 'icon' => '✅', 'label' => 'Absensi'])
                            @include('partials.navigation-link', ['href' => $safeRoute('student.schedule.index'), 'active' => $safeActive('student.schedule.*'), 'icon' => '🗓️', 'label' => 'Jadwal'])
                            @include('partials.navigation-link', ['href' => $safeRoute('student.calendar.index'), 'active' => $safeActive('student.calendar.*'), 'icon' => '📅', 'label' => 'Kalender'])
                            @include('partials.navigation-link', ['href' => $safeRoute('student.chatbot.index'), 'active' => $safeActive('student.chatbot.*'), 'icon' => '🤖', 'label' => 'AI Chatbot'])
                        </div>
                    </div>
                @endif

                <div>
                    <p class="mb-2 px-3 text-xs font-bold uppercase tracking-wider text-slate-400">Akun</p>
                    <div class="space-y-1">
                        @include('partials.navigation-link', ['href' => $safeRoute('notifications.index'), 'active' => $safeActive('notifications.*'), 'icon' => '🔔', 'label' => 'Notifikasi'])
                        @if(Route::has('logout'))
                            @include('partials.navigation-link', ['href' => route('logout'), 'icon' => '🚪', 'label' => 'Logout', 'method' => 'POST'])
                        @endif
                    </div>
                </div>
            </nav>
        </div>
    </div>
</aside>
