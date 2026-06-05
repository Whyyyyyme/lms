@extends('layouts.app')

@section('title', 'Pengaturan Sistem')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $settings = $settings ?? [];
    $timezones = $timezones ?? ['Asia/Jakarta'];

    $cancelUrl = Route::has('admin.dashboard')
        ? route('admin.dashboard')
        : '#';

    $currentLogo = $settings['logo_path'] ?? null;
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Pengaturan Sistem</h1>

    <p>
        Konfigurasi identitas aplikasi, nama kampus, logo, zona waktu,
        dan catatan kalender akademik untuk LMS Praktikum.
    </p>

    <div class="hero-actions">
        <a href="{{ $cancelUrl }}" class="btn">
            ← Dashboard
        </a>
    </div>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Identitas Aplikasi</h2>
            <div class="section-subtitle">
                Atur nama kampus, nama aplikasi, logo, zona waktu, dan catatan akademik.
            </div>
        </div>

        @if(!empty($settings['updated_at']))
            <span class="status-pill status-muted">
                Diperbarui: {{ $settings['updated_at'] }}
            </span>
        @endif
    </div>

    <form
        action="{{ route('admin.settings.update') }}"
        method="POST"
        enctype="multipart/form-data"
        class="form-card"
    >
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group">
                <label for="campus_name" class="form-label">
                    Nama Kampus <span class="required">*</span>
                </label>

                <input
                    id="campus_name"
                    type="text"
                    name="campus_name"
                    class="form-control"
                    value="{{ old('campus_name', $settings['campus_name'] ?? 'Nama Kampus') }}"
                    placeholder="Masukkan nama kampus"
                    required
                >

                <div class="form-help">
                    Nama kampus atau institusi pemilik LMS.
                </div>

                @error('campus_name')
                    <div class="form-help" style="color: var(--danger);">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label for="app_name" class="form-label">
                    Nama Aplikasi <span class="required">*</span>
                </label>

                <input
                    id="app_name"
                    type="text"
                    name="app_name"
                    class="form-control"
                    value="{{ old('app_name', $settings['app_name'] ?? 'LMS Praktikum') }}"
                    placeholder="Masukkan nama aplikasi"
                    required
                >

                <div class="form-help">
                    Nama aplikasi yang akan ditampilkan di sistem.
                </div>

                @error('app_name')
                    <div class="form-help" style="color: var(--danger);">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label for="logo" class="form-label">
                    Logo Aplikasi
                </label>

                @if(!empty($currentLogo))
                    <div
                        style="
                            margin-bottom: 14px;
                            padding: 14px;
                            border: 1px solid var(--line);
                            border-radius: 18px;
                            background: #f8fafc;
                        "
                    >
                        <div class="form-help" style="margin-bottom: 10px;">
                            Logo saat ini
                        </div>

                        <img
                            src="{{ asset('storage/' . $currentLogo) }}"
                            alt="Logo aplikasi"
                            style="
                                height: 72px;
                                width: auto;
                                max-width: 180px;
                                border-radius: 14px;
                                border: 1px solid var(--line);
                                padding: 8px;
                                background: #ffffff;
                                object-fit: contain;
                            "
                        >
                    </div>
                @else
                    <div
                        style="
                            margin-bottom: 14px;
                            padding: 14px;
                            border: 1px solid var(--line);
                            border-radius: 18px;
                            background: #f8fafc;
                            color: #64748b;
                            font-size: 14px;
                        "
                    >
                        Belum ada logo aplikasi.
                    </div>
                @endif

                <input
                    id="logo"
                    name="logo"
                    type="file"
                    accept="image/jpeg,image/png,image/webp"
                    class="form-control"
                >

                <div class="form-help">
                    Opsional. Maksimal 5 MB. Format: JPG, JPEG, PNG, atau WEBP.
                </div>

                @error('logo')
                    <div class="form-help" style="color: var(--danger);">
                        {{ $message }}
                    </div>
                @enderror

                @if(!empty($currentLogo))
                    <label
                        style="
                            display: inline-flex;
                            align-items: center;
                            gap: 10px;
                            margin-top: 12px;
                            padding: 12px 14px;
                            border: 1px solid var(--line);
                            border-radius: 16px;
                            background: #ffffff;
                            cursor: pointer;
                        "
                    >
                        <input
                            type="checkbox"
                            name="remove_logo"
                            value="1"
                            @checked(old('remove_logo'))
                            style="width: 16px; height: 16px;"
                        >

                        <span style="font-weight: 800; color: #334155;">
                            Hapus logo saat ini
                        </span>
                    </label>
                @endif
            </div>

            <div class="form-group">
                <label for="timezone" class="form-label">
                    Zona Waktu <span class="required">*</span>
                </label>

                <select
                    id="timezone"
                    name="timezone"
                    class="form-control"
                    required
                >
                    @foreach($timezones as $timezone)
                        <option
                            value="{{ $timezone }}"
                            @selected(old('timezone', $settings['timezone'] ?? 'Asia/Jakarta') === $timezone)
                        >
                            {{ $timezone }}
                        </option>
                    @endforeach
                </select>

                <div class="form-help">
                    Disarankan memakai Asia/Jakarta untuk sistem kampus di WIB.
                </div>

                @error('timezone')
                    <div class="form-help" style="color: var(--danger);">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group" style="grid-column: 1 / -1;">
                <label for="academic_calendar_note" class="form-label">
                    Catatan Kalender Akademik
                </label>

                <textarea
                    id="academic_calendar_note"
                    name="academic_calendar_note"
                    class="form-control"
                    placeholder="Contoh: Praktikum aktif mulai minggu ke-2 perkuliahan. UTS minggu ke-8, UAS minggu ke-16."
                    style="min-height: 140px;"
                >{{ old('academic_calendar_note', $settings['academic_calendar_note'] ?? '') }}</textarea>

                <div class="form-help">
                    Catatan internal untuk admin terkait kalender akademik.
                </div>

                @error('academic_calendar_note')
                    <div class="form-help" style="color: var(--danger);">
                        {{ $message }}
                    </div>
                @enderror
            </div>
        </div>

        <div class="alert" style="margin-top: 16px;">
            <strong>Catatan:</strong>
            Pengaturan ini akan memengaruhi identitas aplikasi yang tampil di halaman LMS.
            Untuk menampilkan logo, pastikan storage link sudah dibuat.
        </div>

        <div class="form-actions">
            <a href="{{ $cancelUrl }}" class="btn">
                Batal
            </a>

            <button type="submit" class="btn btn-primary">
                Simpan Pengaturan
            </button>
        </div>
    </form>
</section>
@endsection