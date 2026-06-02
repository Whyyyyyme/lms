<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Registrasi Mahasiswa - LMS Praktikum</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
    <div class="flex min-h-screen items-center justify-center px-4 py-10">
        <div class="w-full max-w-2xl">
            <div class="mb-6 text-center">
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Registrasi Mahasiswa</h1>
                <p class="mt-2 text-sm text-slate-600">Daftar memakai email aktif. Akun akan menunggu verifikasi admin sebelum bisa login.</p>
            </div>

            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
                @if ($errors->any())
                    <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.store') }}" class="grid gap-5 md:grid-cols-2">
                    @csrf

                    <div class="md:col-span-2">
                        <label for="name" class="mb-1 block text-sm font-medium text-slate-700">Nama Lengkap</label>
                        <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    </div>

                    <div>
                        <label for="nim_nip" class="mb-1 block text-sm font-medium text-slate-700">NIM</label>
                        <input id="nim_nip" name="nim_nip" type="text" value="{{ old('nim_nip') }}" required class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    </div>

                    <div>
                        <label for="email" class="mb-1 block text-sm font-medium text-slate-700">Email Aktif</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="username" class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    </div>

                    <div class="md:col-span-2">
                        <label for="study_semester_id" class="mb-1 block text-sm font-medium text-slate-700">Semester Mahasiswa</label>
                        <select id="study_semester_id" name="study_semester_id" required class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                            <option value="">Pilih semester</option>
                            @foreach ($studySemesters as $semester)
                                <option value="{{ $semester->id }}" @selected((string) old('study_semester_id') === (string) $semester->id)>
                                    {{ $semester->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-sm font-medium text-slate-700">Password LMS</label>
                        <input id="password" name="password" type="password" required autocomplete="new-password" class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                        <p class="mt-1 text-xs text-slate-500">Password ini khusus untuk LMS, tidak harus sama dengan password Gmail.</p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">Konfirmasi Password</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200">
                    </div>

                    <div class="md:col-span-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-blue-600 hover:text-blue-700">Sudah punya akun? Login</a>
                        <button type="submit" class="rounded-xl bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            Daftar & Tunggu Verifikasi
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
