<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    private const SETTINGS_FILE = 'lms-settings.json';

    public function edit(): View
    {
        $settings = $this->settings();

        return view('admin.settings.edit', [
            'settings' => $settings,
            'timezones' => timezone_identifiers_list(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campus_name' => ['required', 'string', 'max:255'],
            'app_name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_logo' => ['nullable', 'boolean'],
            'timezone' => ['required', 'string', Rule::in(timezone_identifiers_list())],
            'academic_calendar_note' => ['nullable', 'string', 'max:5000'],
        ], [
            'campus_name.required' => 'Nama kampus wajib diisi.',
            'app_name.required' => 'Nama aplikasi wajib diisi.',
            'timezone.required' => 'Zona waktu wajib dipilih.',
            'timezone.in' => 'Zona waktu tidak valid.',
            'logo.uploaded' => 'Logo gagal diunggah. Cek ukuran file dan konfigurasi upload di php.ini.',
            'logo.max' => 'Ukuran logo maksimal 5 MB.',
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.mimes' => 'Logo harus berformat JPG, JPEG, PNG, atau WEBP.',
        ]);

        $settings = $this->settings();
        $currentLogoPath = $settings['logo_path'] ?? null;

        $logoPath = $currentLogoPath;

        if ($request->boolean('remove_logo')) {
            if ($currentLogoPath && Storage::disk('public')->exists($currentLogoPath)) {
                Storage::disk('public')->delete($currentLogoPath);
            }

            $logoPath = null;
        }

        if ($request->hasFile('logo')) {
            if ($currentLogoPath && Storage::disk('public')->exists($currentLogoPath)) {
                Storage::disk('public')->delete($currentLogoPath);
            }

            $logoPath = $request->file('logo')->store('settings', 'public');
        }

        $data = [
            'campus_name' => $validated['campus_name'],
            'app_name' => $validated['app_name'],
            'logo_path' => $logoPath,
            'timezone' => $validated['timezone'],
            'academic_calendar_note' => $validated['academic_calendar_note'] ?? null,
            'updated_at' => now()->toDateTimeString(),
        ];

        Storage::disk('local')->put(
            self::SETTINGS_FILE,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return back()->with('success', 'Pengaturan sistem berhasil disimpan.');
    }

    private function settings(): array
    {
        $defaults = [
            'campus_name' => 'Nama Kampus',
            'app_name' => 'LMS Praktikum',
            'logo_path' => null,
            'timezone' => 'Asia/Jakarta',
            'academic_calendar_note' => null,
            'updated_at' => null,
        ];

        if (! Storage::disk('local')->exists(self::SETTINGS_FILE)) {
            return $defaults;
        }

        $stored = json_decode(Storage::disk('local')->get(self::SETTINGS_FILE), true);

        if (! is_array($stored)) {
            return $defaults;
        }

        return array_merge($defaults, $stored);
    }
}