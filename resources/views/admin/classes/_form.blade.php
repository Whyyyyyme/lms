<div class="grid gap-5 md:grid-cols-2">
    @include('partials.form.select', ['label' => 'Matakuliah', 'name' => 'course_id', 'required' => true])
        @foreach ($courses as $course)
            <option value="{{ $course->id }}" @selected((string) old('course_id', $praktikumClass->course_id ?? '') === (string) $course->id)>
                {{ $course->studySemester?->name ?? 'Tanpa Semester' }} - {{ $course->name }} - {{ $course->academicYear?->year }}
            </option>
        @endforeach
    </select>

    @include('partials.form.select', ['label' => 'Asisten Praktikum', 'name' => 'assistant_id'])
        <option value="">Belum ditentukan</option>
        @foreach ($assistants as $assistant)
            <option value="{{ $assistant->id }}" @selected((string) old('assistant_id', $praktikumClass->assistant_id ?? '') === (string) $assistant->id)>{{ $assistant->name }}</option>
        @endforeach
    </select>

    @include('partials.form.input', ['label' => 'Nama Kelas', 'name' => 'name', 'value' => $praktikumClass->name ?? null, 'required' => true])
    @include('partials.form.input', ['label' => 'Ruangan', 'name' => 'room', 'value' => $praktikumClass->room ?? null])
    <div class="md:col-span-2">@include('partials.form.input', ['label' => 'Jadwal', 'name' => 'schedule', 'value' => $praktikumClass->schedule ?? null, 'placeholder' => 'Senin, 10.00 - 12.00'])</div>
</div>

<div class="mt-5">@include('partials.form.checkbox', ['label' => 'Kelas aktif', 'name' => 'is_active', 'checked' => $praktikumClass->is_active ?? true])</div>

<div class="mt-6">
    <p class="mb-2 text-sm font-semibold text-slate-700">Mahasiswa Kelas</p>
    <p class="mb-3 text-xs text-slate-500">Opsional. Mahasiswa juga bisa mendapat akses otomatis berdasarkan semester. Checklist ini dipakai jika kelas perlu pembagian khusus.</p>
    @php($selectedStudents = collect(old('student_ids', isset($praktikumClass) ? $praktikumClass->students->pluck('id')->all() : []))->map(fn($id) => (string) $id)->all())
    <div class="grid max-h-72 gap-2 overflow-y-auto rounded-2xl border border-slate-200 p-4 md:grid-cols-2">
        @foreach ($students as $student)
            <label class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 text-sm">
                <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" @checked(in_array((string) $student->id, $selectedStudents, true)) class="rounded border-slate-300 text-indigo-600">
                <span>{{ $student->name }} <span class="text-slate-400">({{ $student->nim_nip }} • {{ $student->studySemester?->name ?? 'Tanpa Semester' }})</span></span>
            </label>
        @endforeach
    </div>
</div>
