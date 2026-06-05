@php
    $isActiveChecked = old(
        'is_active',
        isset($studySemester) ? (bool) $studySemester->is_active : true
    );
@endphp

<div class="form-grid">
    <div class="form-group">
        <label for="level" class="form-label">
            Level Semester <span class="required">*</span>
        </label>

        <input
            id="level"
            type="number"
            name="level"
            class="form-control"
            value="{{ old('level', $studySemester->level ?? null) }}"
            placeholder="Contoh: 1"
            min="1"
            required
        >

        <div class="form-help">
            Isi angka semester, misalnya 1 sampai 8.
        </div>

        @error('level')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="name" class="form-label">
            Nama Semester <span class="required">*</span>
        </label>

        <input
            id="name"
            type="text"
            name="name"
            class="form-control"
            value="{{ old('name', $studySemester->name ?? null) }}"
            placeholder="Contoh: Semester 1"
            required
        >

        <div class="form-help">
            Nama yang akan tampil di form mahasiswa dan mata kuliah.
        </div>

        @error('name')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group" style="grid-column: 1 / -1;">
        <label for="description" class="form-label">
            Deskripsi
        </label>

        <textarea
            id="description"
            name="description"
            class="form-control"
            placeholder="Contoh: Semester mahasiswa tingkat 1"
        >{{ old('description', $studySemester->description ?? null) }}</textarea>

        <div class="form-help">
            Deskripsi bersifat opsional, tetapi dapat membantu admin memahami penggunaan semester ini.
        </div>

        @error('description')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group" style="grid-column: 1 / -1;">
        <label class="form-label">
            Status Semester
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
                Semester aktif
            </span>
        </label>

        <div class="form-help">
            Semester nonaktif tidak akan muncul di form tambah mahasiswa dan mata kuliah baru.
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
    Semester menjadi dasar akses mahasiswa ke mata kuliah dan kelas praktikum.
    Jika semester dinonaktifkan, data lama tetap tersimpan, tetapi semester tersebut tidak dipakai untuk input baru.
</div>