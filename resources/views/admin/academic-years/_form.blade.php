<div class="grid gap-5 md:grid-cols-2">
    @include('partials.form.input', [
        'label' => 'Tahun Akademik',
        'name' => 'year',
        'value' => $academicYear->year ?? null,
        'placeholder' => 'Contoh: 2025/2026',
        'required' => true,
        'help' => 'Format bebas, tapi disarankan seperti 2025/2026.'
    ])

    <label class="form-group" for="semester">
        <span class="form-label">Periode <span class="required">*</span></span>

        <select id="semester" name="semester" required class="form-control">
            <option value="">Pilih periode</option>
            <option value="ganjil" @selected(old('semester', $academicYear->semester ?? '') === 'ganjil')>
                Ganjil
            </option>
            <option value="genap" @selected(old('semester', $academicYear->semester ?? '') === 'genap')>
                Genap
            </option>
        </select>

        <small class="form-help">
            Ini adalah periode tahun akademik, bukan semester mahasiswa.
        </small>
    </label>
</div>

<div class="mt-5">
    @include('partials.form.checkbox', [
        'label' => 'Jadikan tahun akademik aktif',
        'name' => 'is_active',
        'checked' => $academicYear->is_active ?? false
    ])
</div>

<p class="mt-3 rounded-xl bg-blue-50 px-4 py-3 text-sm text-blue-700">
    Hanya satu tahun akademik yang boleh aktif. Jika data ini dijadikan aktif, tahun akademik lain otomatis menjadi nonaktif.
</p>