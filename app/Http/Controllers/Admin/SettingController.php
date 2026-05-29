<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        $settings = $this->settings();

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'campus_name' => ['required', 'string', 'max:255'],
            'app_name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:5120'],
            'timezone' => ['required', 'string', 'max:100'],
            'academic_calendar_note' => ['nullable', 'string'],
        ], [
            'logo.uploaded' => 'Logo gagal diunggah. Cek ukuran file dan konfigurasi upload di php.ini.',
            'logo.max' => 'Ukuran logo maksimal 5 MB.',
            'logo.image' => 'Logo harus berupa gambar.',
        ]);

        $settings = $this->settings();

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('settings', 'public');
        } else {
            $validated['logo_path'] = $settings['logo_path'] ?? null;
        }

        unset($validated['logo']);

        Storage::disk('local')->put('lms-settings.json', json_encode($validated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return back()->with('success', 'Pengaturan sistem berhasil disimpan.');
    }

    private function settings(): array
    {
        if (! Storage::disk('local')->exists('lms-settings.json')) {
            return [
                'campus_name' => 'Nama Kampus',
                'app_name' => 'LMS Praktikum',
                'logo_path' => null,
                'timezone' => 'Asia/Jakarta',
                'academic_calendar_note' => null,
            ];
        }

        return json_decode(Storage::disk('local')->get('lms-settings.json'), true) ?: [];
    }
}
