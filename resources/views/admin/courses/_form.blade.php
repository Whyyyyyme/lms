<div class="grid gap-5 md:grid-cols-2">
    @include('partials.form.select', ['label' => 'Tahun Akademik', 'name' => 'academic_year_id', 'required' => true])
        @foreach ($academicYears as $year)
            <option value="{{ $year->id }}" @selected((string) old('academic_year_id', $course->academic_year_id ?? '') === (string) $year->id)>{{ $year->year }} - {{ ucfirst($year->semester) }}</option>
        @endforeach
    </select>

    @include('partials.form.select', ['label' => 'Semester Mahasiswa', 'name' => 'study_semester_id', 'required' => true])
        <option value="">Pilih semester mahasiswa</option>
        @foreach ($studySemesters as $semester)
            <option value="{{ $semester->id }}" @selected((string) old('study_semester_id', $course->study_semester_id ?? '') === (string) $semester->id)>{{ $semester->name }}</option>
        @endforeach
    </select>

    @include('partials.form.input', ['label' => 'Nama Matakuliah', 'name' => 'name', 'value' => $course->name ?? null, 'required' => true])
    @include('partials.form.input', ['label' => 'Kode', 'name' => 'code', 'value' => $course->code ?? null, 'required' => true])
    @include('partials.form.input', ['label' => 'SKS', 'name' => 'sks', 'type' => 'number', 'value' => $course->sks ?? 1, 'required' => true])
</div>

<p class="mt-3 rounded-xl bg-blue-50 px-4 py-3 text-sm text-blue-700">
    Contoh: Semester 3 dapat berisi Pemrograman Web, Basis Data, dan Jaringan Komputer. Mahasiswa Semester 3 akan bisa melihat kelas/materi/tugas dari matakuliah semester tersebut.
</p>

<div class="mt-5">@include('partials.form.checkbox', ['label' => 'Matakuliah aktif', 'name' => 'is_active', 'checked' => $course->is_active ?? true])</div>
