@extends('layouts.app', ['title' => 'Pengaturan Sistem'])

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Pengaturan Sistem',
    'description' => 'Konfigurasi nama aplikasi, kampus, logo, zona waktu, dan catatan kalender akademik.',
])

@include('partials.validation-errors')

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
    @csrf
    @method('PUT')

    <div class="grid gap-5 md:grid-cols-2">
        @include('partials.form.input', [
            'label' => 'Nama Kampus',
            'name' => 'campus_name',
            'value' => old('campus_name', $settings['campus_name'] ?? 'Nama Kampus'),
            'required' => true,
        ])

        @include('partials.form.input', [
            'label' => 'Nama Aplikasi',
            'name' => 'app_name',
            'value' => old('app_name', $settings['app_name'] ?? 'LMS Praktikum'),
            'required' => true,
        ])

        <div>
            <label for="logo" class="mb-1 block text-sm font-semibold text-slate-700">Logo Aplikasi</label>
            <input id="logo" name="logo" type="file" accept="image/*" class="block w-full rounded-2xl border border-slate-300 px-3 py-2 text-sm">
            <p class="mt-1 text-xs text-slate-500">Opsional. Maksimal 5 MB. Format gambar umum seperti JPG, PNG, atau WEBP.</p>
            @error('logo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        @include('partials.form.input', [
            'label' => 'Zona Waktu',
            'name' => 'timezone',
            'value' => old('timezone', $settings['timezone'] ?? 'Asia/Jakarta'),
            'required' => true,
        ])
    </div>

    <div class="mt-5">
        @include('partials.form.textarea', [
            'label' => 'Catatan Kalender Akademik',
            'name' => 'academic_calendar_note',
            'value' => old('academic_calendar_note', $settings['academic_calendar_note'] ?? ''),
        ])
    </div>

    @include('partials.form.actions', ['cancel' => route('admin.dashboard'), 'label' => 'Simpan Pengaturan'])
</form>
@endsection
