@php
    $isActiveChecked = old(
        'is_active',
        isset($academicYear) ? (bool) $academicYear->is_active : false
    );
@endphp

<div class="form-grid">
    <div class="form-group">
        <label for="year" class="form-label">
            Tahun Akademik <span class="required">*</span>
        </label>

        <input
            id="year"
            type="text"
            name="year"
            class="form-control"
            value="{{ old('year', $academicYear->year ?? null) }}"
            placeholder="Contoh: 2025/2026"
            required
        >

        <div class="form-help">
            Format bebas, tetapi disarankan seperti 2025/2026.
        </div>

        @error('year')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="semester" class="form-label">
            Periode <span class="required">*</span>
        </label>

        <select
            id="semester"
            name="semester"
            class="form-control"
            required
        >
            <option value="">Pilih periode</option>

            <option value="ganjil" @selected(old('semester', $academicYear->semester ?? '') === 'ganjil')>
                Ganjil
            </option>

            <option value="genap" @selected(old('semester', $academicYear->semester ?? '') === 'genap')>
                Genap
            </option>
        </select>

        <div class="form-help">
            Ini adalah periode tahun akademik, bukan semester mahasiswa.
        </div>

        @error('semester')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group" style="grid-column: 1 / -1;">
        <label class="form-label">
            Status Tahun Akademik
        </label>

        <input type="hidden" name="is_active" value="0">

        <label
            style="
                display: inline-flex;
                align-items: center;
                gap: 10px;
                padding: 12px 14px;
                border: 1px solid var(--line);
                border-radius: 16px;
                background: #f8fafc;
                cursor: pointer;
            "
        >
            <input
                type="checkbox"
                name="is_active"
                value="1"
                @checked($isActiveChecked)
                style="width: 16px; height: 16px;"
            >

            <span style="font-weight: 800; color: #334155;">
                Jadikan tahun akademik aktif
            </span>
        </label>

        <div class="form-help">
            Jika diaktifkan, tahun akademik lain otomatis menjadi nonaktif sesuai logic controller.
        </div>

        @error('is_active')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>
</div>

<div class="alert" style="margin-top: 16px;">
    <strong>Catatan:</strong>
    Hanya satu tahun akademik yang boleh aktif. Jika data ini dijadikan aktif,
    tahun akademik lain otomatis menjadi nonaktif.
</div>