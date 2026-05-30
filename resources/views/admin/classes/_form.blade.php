@php
    $selectedStudents = collect(
        old('student_ids', isset($praktikumClass) ? $praktikumClass->students->pluck('id')->all() : [])
    )->map(fn ($id) => (string) $id)->all();

    $selectedCourseId = old('course_id', $praktikumClass->course_id ?? '');
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <label class="form-group" for="course_id">
        <span class="form-label">Mata Kuliah <span class="required">*</span></span>

        <select id="course_id" name="course_id" required class="form-control">
            <option value="">Pilih mata kuliah</option>
            @foreach ($courses as $course)
                <option
                    value="{{ $course->id }}"
                    data-semester-id="{{ $course->study_semester_id }}"
                    @selected((string) $selectedCourseId === (string) $course->id)
                >
                    {{ $course->studySemester?->name ?? 'Tanpa Semester' }}
                    -
                    {{ $course->code }}
                    -
                    {{ $course->name }}
                    -
                    {{ $course->academicYear?->year ?? '-' }}
                    @if($course->academicYear)
                        {{ ucfirst($course->academicYear->semester) }}
                    @endif
                </option>
            @endforeach
        </select>

        <small class="form-help">
            Kelas akan mengikuti semester dari mata kuliah yang dipilih.
        </small>
    </label>

    <label class="form-group" for="assistant_id">
        <span class="form-label">Asisten Praktikum</span>

        <select id="assistant_id" name="assistant_id" class="form-control">
            <option value="">Belum ditentukan</option>
            @foreach ($assistants as $assistant)
                <option value="{{ $assistant->id }}" @selected((string) old('assistant_id', $praktikumClass->assistant_id ?? '') === (string) $assistant->id)>
                    {{ $assistant->name }}
                    @if($assistant->nim_nip)
                        - {{ $assistant->nim_nip }}
                    @endif
                </option>
            @endforeach
        </select>

        <small class="form-help">
            Asisten dapat diganti kapan saja.
        </small>
    </label>

    @include('partials.form.input', [
        'label' => 'Nama Kelas',
        'name' => 'name',
        'value' => $praktikumClass->name ?? null,
        'placeholder' => 'Contoh: Kelas A',
        'required' => true
    ])

    @include('partials.form.input', [
        'label' => 'Ruangan',
        'name' => 'room',
        'value' => $praktikumClass->room ?? null,
        'placeholder' => 'Contoh: Lab Komputer 1'
    ])

    <div class="md:col-span-2">
        @include('partials.form.input', [
            'label' => 'Jadwal',
            'name' => 'schedule',
            'value' => $praktikumClass->schedule ?? null,
            'placeholder' => 'Contoh: Senin, 10.00 - 12.00'
        ])
    </div>
</div>

<div class="mt-5">
    @include('partials.form.checkbox', [
        'label' => 'Kelas aktif',
        'name' => 'is_active',
        'checked' => $praktikumClass->is_active ?? true
    ])
</div>

<div class="mt-6">
    <p class="mb-2 text-sm font-semibold text-slate-700">
        Mahasiswa Manual / Khusus
    </p>

    <p class="mb-3 text-xs text-slate-500">
        Opsional. Mahasiswa sebenarnya sudah mendapat akses otomatis berdasarkan semester.
        Checklist ini hanya dipakai kalau kelas perlu pembagian khusus.
    </p>

    <div id="students-wrapper" class="grid max-h-72 gap-2 overflow-y-auto rounded-2xl border border-slate-200 p-4 md:grid-cols-2">
        @foreach ($students as $student)
            <label
                class="student-option flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 text-sm"
                data-semester-id="{{ $student->study_semester_id }}"
            >
                <input
                    type="checkbox"
                    name="student_ids[]"
                    value="{{ $student->id }}"
                    @checked(in_array((string) $student->id, $selectedStudents, true))
                    class="rounded border-slate-300 text-indigo-600"
                >

                <span>
                    {{ $student->name }}
                    <span class="text-slate-400">
                        ({{ $student->nim_nip ?? '-' }} • {{ $student->studySemester?->name ?? 'Tanpa Semester' }})
                    </span>
                </span>
            </label>
        @endforeach
    </div>

    <p id="student-filter-note" class="mt-3 text-xs text-slate-500">
        Pilih mata kuliah terlebih dahulu agar daftar mahasiswa disesuaikan dengan semester mata kuliah.
    </p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const courseSelect = document.getElementById('course_id');
        const studentOptions = document.querySelectorAll('.student-option');
        const note = document.getElementById('student-filter-note');

        function filterStudentsByCourseSemester() {
            const selectedOption = courseSelect.options[courseSelect.selectedIndex];
            const selectedSemesterId = selectedOption ? selectedOption.dataset.semesterId : '';

            let visibleCount = 0;

            studentOptions.forEach(function (option) {
                const studentSemesterId = option.dataset.semesterId;
                const checkbox = option.querySelector('input[type="checkbox"]');

                if (!selectedSemesterId || studentSemesterId === selectedSemesterId) {
                    option.style.display = '';
                    visibleCount++;
                } else {
                    option.style.display = 'none';
                    checkbox.checked = false;
                }
            });

            if (!selectedSemesterId) {
                note.textContent = 'Pilih mata kuliah terlebih dahulu agar daftar mahasiswa disesuaikan dengan semester mata kuliah.';
            } else if (visibleCount === 0) {
                note.textContent = 'Belum ada mahasiswa aktif pada semester mata kuliah ini.';
            } else {
                note.textContent = 'Daftar mahasiswa sudah difilter berdasarkan semester mata kuliah.';
            }
        }

        courseSelect.addEventListener('change', filterStudentsByCourseSemester);
        filterStudentsByCourseSemester();
    });
</script>