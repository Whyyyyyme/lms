<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'LMS Praktikum' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen bg-slate-100 text-slate-800 antialiased">
    <div class="min-h-screen">
        <nav class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-indigo-600 text-sm font-bold text-white">LMS</div>
                    <div>
                        <p class="font-bold leading-tight text-slate-900">LMS Praktikum</p>
                        <p class="text-xs text-slate-500">Sistem Pembelajaran Praktikum</p>
                    </div>
                </a>

                <div class="hidden items-center gap-2 md:flex">
                    @auth
                        @role('admin')
                            <a href="{{ route('admin.dashboard') }}" class="rounded-xl px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Dashboard Admin</a>
                            <a href="{{ route('admin.users.index') }}" class="rounded-xl px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">User</a>
                            <a href="{{ route('admin.kelas.index') }}" class="rounded-xl px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Kelas</a>
                        @endrole
                        @role('asisten')
                            <a href="{{ route('assistant.dashboard') }}" class="rounded-xl px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Dashboard</a>
                            <a href="{{ route('assistant.materi.index') }}" class="rounded-xl px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Materi</a>
                            <a href="{{ route('assistant.tugas.index') }}" class="rounded-xl px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Tugas</a>
                            <a href="{{ route('assistant.attendances.index') }}" class="rounded-xl px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Absensi</a>
                        @endrole
                        @role('mahasiswa')
                            <a href="{{ route('student.dashboard') }}" class="rounded-xl px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Dashboard</a>
                            <a href="{{ route('student.materials.index') }}" class="rounded-xl px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Materi</a>
                            <a href="{{ route('student.assignments.index') }}" class="rounded-xl px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">Tugas</a>
                            <a href="{{ route('student.chatbot.index') }}" class="rounded-xl px-3 py-2 text-sm font-medium text-slate-600 hover:bg-slate-100">AI Chatbot</a>
                        @endrole
                    @endauth
                </div>

                <div class="flex items-center gap-3">
                    @auth
                        @if (class_exists(\Livewire\Livewire::class))
                            <livewire:notification-dropdown />
                        @endif
                        <div class="hidden text-right sm:block">
                            <p class="text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-slate-500">{{ auth()->user()->roles->pluck('name')->join(', ') ?: 'User' }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-xl border border-slate-200 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-100">
                                Logout
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700">
                            Login
                        </a>
                    @endauth
                </div>
            </div>
        </nav>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            @include('partials.flash')
            @include('partials.validation-errors')
            @yield('content')
        </main>
    </div>
    @livewireScripts
</body>
</html>
