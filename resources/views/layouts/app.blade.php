@php
    use Illuminate\Support\Facades\Route;

    $user = auth()->user();
    $appName = config('app.name', 'LMS Praktikum');
    $pageTitle = trim($__env->yieldContent('title')) ?: $appName;
@endphp
<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-100">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }} - {{ $appName }}</title>

    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endif

    @livewireStyles
    @stack('styles')
</head>
<body class="min-h-full bg-slate-100 text-slate-900 antialiased">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen">
        @include('partials.sidebar')

        <div class="lg:pl-72">
            @include('partials.navbar')

            <main class="px-4 py-6 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl space-y-6">
                    @include('partials.flash')
                    @include('partials.validation-errors')
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @livewireScripts

    {{-- Listener real-time opsional. Aman walaupun file listener belum ada. --}}
    @includeIf('components.notification-echo-listener')

    @stack('scripts')
</body>
</html>
