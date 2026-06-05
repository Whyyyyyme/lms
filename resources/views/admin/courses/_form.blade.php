@php
    $academicYears = $academicYears ?? collect();
    $studySemesters = $studySemesters ?? collect();

    $isActiveChecked = old(
        'is_active',
        isset($course) ? (bool) $course->is_active : true
    );
@endphp

<div class="form-grid">
    <div class="form-group">
        <label for="academic_year_id" class="form-label">
            Tahun Akademik <span class="required">*</span>
        </label>

        <select
            id="academic_year_id"
            name="academic_year_id"
            class="form-control"
            required
        >
            <option value="">Pilih tahun akademik</option>

            @foreach ($academicYears as $year)
                <option
                    value="{{ $year->id }}"
                    @selected((string) old('academic_year_id', $course->academic_year_id ?? '') === (string) $year->id)
                >
                    {{ $year->year }} - {{ ucfirst($year->semester) }}
                    {{ $year->is_active ? '(Aktif)' : '' }}
                </option>
            @endforeach
        </select>

        <div class="form-help">
            Tahun akademik digunakan untuk periode penyelenggaraan mata kuliah.
        </div>

        @error('academic_year_id')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="study_semester_id" class="form-label">
            Semester Mahasiswa <span class="required">*</span>
        </label>

        <select
            id="study_semester_id"
            name="study_semester_id"
            class="form-control"
            required
        >
            <option value="">Pilih semester mahasiswa</option>

            @foreach ($studySemesters as $semester)
                <option
                    value="{{ $semester->id }}"
                    @selected((string) old('study_semester_id', $course->study_semester_id ?? '') === (string) $semester->id)
                >
                    {{ $semester->name }}
                </option>
            @endforeach
        </select>

        <div class="form-help">
            Mahasiswa pada semester ini akan melihat mata kuliah ini.
        </div>

        @error('study_semester_id')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="name" class="form-label">
            Nama Mata Kuliah <span class="required">*</span>
        </label>

        <input
            id="name"
            type="text"
            name="name"
            class="form-control"
            value="{{ old('name', $course->name ?? null) }}"
            placeholder="Contoh: Pemrograman Web"
            required
        >

        <div class="form-help">
            Nama mata kuliah yang akan tampil untuk admin, asisten, dan mahasiswa.
        </div>

        @error('name')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="code" class="form-label">
            Kode Mata Kuliah <span class="required">*</span>
        </label>

        <input
            id="code"
            type="text"
            name="code"
            class="form-control"
            value="{{ old('code', $course->code ?? null) }}"
            placeholder="Contoh: IF301"
            required
        >

        <div class="form-help">
            Kode mata kuliah digunakan sebagai identitas singkat mata kuliah.
        </div>

        @error('code')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="sks" class="form-label">
            SKS <span class="required">*</span>
        </label>

        <input
            id="sks"
            type="number"
            name="sks"
            class="form-control"
            value="{{ old('sks', $course->sks ?? 1) }}"
            min="1"
            max="6"
            required
        >

        <div class="form-help">
            Isi jumlah SKS mata kuliah. Umumnya 1 sampai 3.
        </div>

        @error('sks')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label class="form-label">
            Status Mata Kuliah
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
                Mata kuliah aktif
            </span>
        </label>

        <div class="form-help">
            Mata kuliah aktif dapat digunakan pada kelas praktikum dan ditampilkan sesuai aturan akses.
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
    Contoh: Semester 3 bisa berisi Pemrograman Web, Basis Data, dan Jaringan Komputer.
    Mahasiswa Semester 3 akan melihat mata kuliah yang terhubung ke Semester 3.
</div>