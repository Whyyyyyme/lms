<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - LMS Praktikum</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">
            <div class="mb-6 text-center">
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">LMS Praktikum</h1>
                <p class="mt-2 text-sm text-slate-600">Masuk untuk mengakses dashboard praktikum.</p>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                @if (session('status'))
                    <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                            placeholder="nama@gmail.com"
                        >
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password</label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                            placeholder="Masukkan password"
                        >
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                            <input type="checkbox" name="remember" value="1" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                            Ingat saya
                        </label>
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        Masuk
                    </button>
                </form>
                <div class="mt-5 text-center text-sm text-slate-600">
                    Belum punya akun mahasiswa?
                    <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-700">
                        Register di sini
                    </a>
                </div>

                @if (\Illuminate\Support\Facades\Route::has('register'))
                    <div class="mt-4 text-center text-sm text-slate-600">
                        Belum punya akun mahasiswa?
                        <a href="{{ route('register') }}" class="font-semibold text-blue-600 hover:text-blue-700">Daftar di sini</a>
                    </div>
                @endif

                <div class="mt-6 rounded-xl bg-slate-50 p-4 text-xs text-slate-600">
                    <p class="font-semibold text-slate-700">Catatan login:</p>
                    <p>Gunakan email yang sudah terdaftar di LMS.</p>
                    <p>Password yang digunakan adalah password khusus LMS, bukan password Gmail/email pribadi.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
