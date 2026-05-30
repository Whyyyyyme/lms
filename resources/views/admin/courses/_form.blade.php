<div class="grid gap-5 md:grid-cols-2">
    <label class="form-group" for="academic_year_id">
        <span class="form-label">Tahun Akademik <span class="required">*</span></span>

        <select id="academic_year_id" name="academic_year_id" required class="form-control">
            <option value="">Pilih tahun akademik</option>
            @foreach ($academicYears as $year)
                <option value="{{ $year->id }}" @selected((string) old('academic_year_id', $course->academic_year_id ?? '') === (string) $year->id)>
                    {{ $year->year }} - {{ ucfirst($year->semester) }}
                    {{ $year->is_active ? '(Aktif)' : '' }}
                </option>
            @endforeach
        </select>

        <small class="form-help">
            Tahun akademik digunakan untuk periode penyelenggaraan mata kuliah.
        </small>
    </label>

    <label class="form-group" for="study_semester_id">
        <span class="form-label">Semester Mahasiswa <span class="required">*</span></span>

        <select id="study_semester_id" name="study_semester_id" required class="form-control">
            <option value="">Pilih semester mahasiswa</option>
            @foreach ($studySemesters as $semester)
                <option value="{{ $semester->id }}" @selected((string) old('study_semester_id', $course->study_semester_id ?? '') === (string) $semester->id)>
                    {{ $semester->name }}
                </option>
            @endforeach
        </select>

        <small class="form-help">
            Mahasiswa pada semester ini akan melihat mata kuliah ini.
        </small>
    </label>

    @include('partials.form.input', [
        'label' => 'Nama Mata Kuliah',
        'name' => 'name',
        'value' => $course->name ?? null,
        'placeholder' => 'Contoh: Pemrograman Web',
        'required' => true
    ])

    @include('partials.form.input', [
        'label' => 'Kode Mata Kuliah',
        'name' => 'code',
        'value' => $course->code ?? null,
        'placeholder' => 'Contoh: IF301',
        'required' => true
    ])

    @include('partials.form.input', [
        'label' => 'SKS',
        'name' => 'sks',
        'type' => 'number',
        'value' => $course->sks ?? 1,
        'required' => true,
        'help' => 'Isi angka 1 sampai 6.'
    ])
</div>

<p class="mt-3 rounded-xl bg-blue-50 px-4 py-3 text-sm text-blue-700">
    Contoh: Semester 3 bisa berisi Pemrograman Web, Basis Data, dan Jaringan Komputer.
    Mahasiswa Semester 3 akan melihat mata kuliah yang terhubung ke Semester 3.
</p>

<div class="mt-5">
    @include('partials.form.checkbox', [
        'label' => 'Mata kuliah aktif',
        'name' => 'is_active',
        'checked' => $course->is_active ?? true
    ])
</div>