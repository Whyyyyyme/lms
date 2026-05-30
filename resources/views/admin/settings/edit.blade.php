@extends('layouts.app')

@section('title', 'Pengaturan Sistem')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Pengaturan Sistem',
    'description' => 'Konfigurasi identitas aplikasi, kampus, logo, zona waktu, dan catatan kalender akademik.',
])

<form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" class="form-card">
    @csrf
    @method('PUT')

    <div class="grid gap-5 md:grid-cols-2">
        @include('partials.form.input', [
            'label' => 'Nama Kampus',
            'name' => 'campus_name',
            'value' => old('campus_name', $settings['campus_name'] ?? 'Nama Kampus'),
            'required' => true,
            'help' => 'Nama kampus atau institusi pemilik LMS.'
        ])

        @include('partials.form.input', [
            'label' => 'Nama Aplikasi',
            'name' => 'app_name',
            'value' => old('app_name', $settings['app_name'] ?? 'LMS Praktikum'),
            'required' => true,
            'help' => 'Nama aplikasi yang akan ditampilkan di sistem.'
        ])

        <div class="form-group">
            <label for="logo" class="form-label">Logo Aplikasi</label>

            @if(!empty($settings['logo_path']))
                <div style="margin-bottom:12px;">
                    <p style="margin-bottom:8px; color:#64748b; font-size:14px;">
                        Logo saat ini:
                    </p>

                    <img
                        src="{{ asset('storage/' . $settings['logo_path']) }}"
                        alt="Logo aplikasi"
                        style="height:64px; width:auto; border-radius:12px; border:1px solid #e2e8f0; padding:6px; background:#fff;"
                    >
                </div>
            @else
                <p style="margin-bottom:12px; color:#64748b; font-size:14px;">
                    Belum ada logo aplikasi.
                </p>
            @endif

            <input
                id="logo"
                name="logo"
                type="file"
                accept="image/jpeg,image/png,image/webp"
                class="form-control"
            >

            <small class="form-help">
                Opsional. Maksimal 5 MB. Format: JPG, JPEG, PNG, atau WEBP.
            </small>

            @error('logo')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror

            @if(!empty($settings['logo_path']))
                <label class="checkbox-row" style="margin-top:12px;">
                    <input type="checkbox" name="remove_logo" value="1" @checked(old('remove_logo'))>
                    <span>Hapus logo saat ini</span>
                </label>
            @endif
        </div>

        <label class="form-group" for="timezone">
            <span class="form-label">Zona Waktu <span class="required">*</span></span>

            <select id="timezone" name="timezone" required class="form-control">
                @foreach($timezones as $timezone)
                    <option value="{{ $timezone }}" @selected(old('timezone', $settings['timezone'] ?? 'Asia/Jakarta') === $timezone)>
                        {{ $timezone }}
                    </option>
                @endforeach
            </select>

            <small class="form-help">
                Disarankan memakai Asia/Jakarta untuk sistem kampus di WIB.
            </small>
        </label>
    </div>

    <div class="mt-5">
        @include('partials.form.textarea', [
            'label' => 'Catatan Kalender Akademik',
            'name' => 'academic_calendar_note',
            'value' => old('academic_calendar_note', $settings['academic_calendar_note'] ?? ''),
            'placeholder' => 'Contoh: Praktikum aktif mulai minggu ke-2 perkuliahan. UTS minggu ke-8, UAS minggu ke-16.',
            'help' => 'Catatan internal untuk admin terkait kalender akademik.'
        ])
    </div>


    @if(!empty($settings['updated_at']))
        <p style="margin-top:12px; color:#64748b; font-size:14px;">
            Terakhir diperbarui: {{ $settings['updated_at'] }}
        </p>
    @endif

    @include('partials.form.actions', [
        'cancel' => route('admin.dashboard'),
        'label' => 'Simpan Pengaturan'
    ])
</form>
@endsection