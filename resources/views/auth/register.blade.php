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
        <div class="w-full max-w-lg">
            <div class="mb-6 text-center">
                <h1 class="text-3xl font-bold tracking-tight text-slate-900">Register Mahasiswa</h1>
                <p class="mt-2 text-sm text-slate-600">
                    Daftar akun LMS Praktikum menggunakan email aktif.
                </p>
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

                <form method="POST" action="{{ route('register.store') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="name" class="mb-1 block text-sm font-medium text-slate-700">
                            Nama Lengkap
                        </label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                            placeholder="Masukkan nama lengkap"
                        >
                    </div>

                    <div>
                        <label for="nim_nip" class="mb-1 block text-sm font-medium text-slate-700">
                            NIM
                        </label>
                        <input
                            id="nim_nip"
                            type="text"
                            name="nim_nip"
                            value="{{ old('nim_nip') }}"
                            required
                            class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                            placeholder="Masukkan NIM"
                        >
                    </div>

                    <div>
                        <label for="email" class="mb-1 block text-sm font-medium text-slate-700">
                            Email Aktif
                        </label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autocomplete="username"
                            class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                            placeholder="contoh: nama@gmail.com"
                        >
                        <p class="mt-1 text-xs text-slate-500">
                            Gunakan email yang benar-benar bisa dibuka. Email ini akan dipakai untuk notifikasi LMS.
                        </p>
                    </div>

                    <div>
                        <label for="study_semester_id" class="mb-1 block text-sm font-medium text-slate-700">
                            Semester Mahasiswa
                        </label>
                        <select
                            id="study_semester_id"
                            name="study_semester_id"
                            required
                            class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                        >
                            <option value="">Pilih semester</option>
                            @foreach ($studySemesters as $semester)
                                <option value="{{ $semester->id }}" @selected((string) old('study_semester_id') === (string) $semester->id)>
                                    {{ $semester->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="student_group" class="mb-1 block text-sm font-medium text-slate-700">
                            Kelas/Rombel Mahasiswa
                        </label>
                        <select
                            id="student_group"
                            name="student_group"
                            required
                            class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                        >
                            <option value="">Pilih kelas/rombel</option>
                            @foreach ($studentGroups as $group)
                                <option value="{{ $group }}" @selected(old('student_group') === $group)>
                                    Kelas {{ $group }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs text-slate-500">
                            Pilih kelas/rombel sesuai kelas perkuliahan kamu, misalnya A, B, C, sampai H.
                        </p>
                    </div>

                    <div>
                        <label for="password" class="mb-1 block text-sm font-medium text-slate-700">
                            Password LMS
                        </label>
                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                            placeholder="Minimal 8 karakter"
                        >
                        <p class="mt-1 text-xs text-slate-500">
                            Password ini khusus untuk login LMS, bukan password Gmail.
                        </p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="mb-1 block text-sm font-medium text-slate-700">
                            Konfirmasi Password LMS
                        </label>
                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            class="block w-full rounded-xl border border-slate-300 px-4 py-2.5 text-sm outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                            placeholder="Ulangi password LMS"
                        >
                    </div>

                    <button type="submit" class="w-full rounded-xl bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        Daftar Mahasiswa
                    </button>
                </form>

                <div class="mt-6 text-center text-sm text-slate-600">
                    Sudah punya akun?
                    <a href="{{ route('login') }}" class="font-semibold text-blue-600 hover:text-blue-700">
                        Login di sini
                    </a>
                </div>

                <div class="mt-6 rounded-xl bg-amber-50 p-4 text-xs text-amber-700">
                    <p class="font-semibold">Catatan:</p>
                    <p>
                        Setelah register, akun belum langsung aktif. Admin harus memverifikasi akun terlebih dahulu.
                    </p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
