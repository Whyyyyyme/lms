@php
    $classTypes = $classTypes ?? [
        'regular' => 'Reguler',
        'combined' => 'Gabungan',
    ];

    $studentGroups = $studentGroups ?? ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

    $selectedStudents = collect(
        old('student_ids', isset($praktikumClass) ? $praktikumClass->students->pluck('id')->all() : [])
    )->map(fn ($id) => (string) $id)->all();

    $selectedCourseId = old('course_id', $praktikumClass->course_id ?? '');

    $selectedClassType = old('class_type', $praktikumClass->class_type ?? 'regular');

    $selectedStudentGroup = old('student_group', $praktikumClass->student_group ?? '');

    $selectedGroupLabel = old('group_label', $praktikumClass->group_label ?? '');

    $selectedGroupMembers = collect(
        old('group_members', isset($praktikumClass) ? ($praktikumClass->group_members ?? []) : [])
    )->map(fn ($group) => (string) $group)->all();
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

    <label class="form-group" for="class_type">
        <span class="form-label">Tipe Kelas <span class="required">*</span></span>

        <select id="class_type" name="class_type" required class="form-control">
            @foreach ($classTypes as $value => $label)
                <option value="{{ $value }}" @selected($selectedClassType === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <small class="form-help">
            Pilih Reguler untuk kelas A-H, atau Gabungan untuk kelas seperti GAB A.
        </small>
    </label>

    <div id="regular-group-wrapper">
        <label class="form-group" for="student_group">
            <span class="form-label">Rombel Reguler <span class="required">*</span></span>

            <select id="student_group" name="student_group" class="form-control">
                <option value="">Pilih rombel</option>
                @foreach ($studentGroups as $group)
                    <option value="{{ $group }}" @selected($selectedStudentGroup === $group)>
                        Kelas {{ $group }}
                    </option>
                @endforeach
            </select>

            <small class="form-help">
                Mahasiswa otomatis diambil dari semester mata kuliah dan rombel ini.
            </small>
        </label>
    </div>

    <div id="combined-label-wrapper">
        <label class="form-group" for="group_label">
            <span class="form-label">Label Kelas Gabungan <span class="required">*</span></span>

            <input
                id="group_label"
                type="text"
                name="group_label"
                value="{{ $selectedGroupLabel }}"
                class="form-control"
                placeholder="Contoh: GAB A"
            >

            <small class="form-help">
                Contoh: GAB A, GAB B, atau Gabungan 1.
            </small>
        </label>
    </div>

    @include('partials.form.input', [
        'label' => 'Nama Kelas',
        'name' => 'name',
        'value' => $praktikumClass->name ?? null,
        'placeholder' => 'Contoh: Kelas E atau GAB A',
        'required' => true
    ])

    @include('partials.form.input', [
        'label' => 'Ruangan',
        'name' => 'room',
        'value' => $praktikumClass->room ?? null,
        'placeholder' => 'Contoh: Lab Komputer 1'
    ])

    <div class="md:col-span-2" id="combined-members-wrapper">
        <p class="mb-2 text-sm font-semibold text-slate-700">
            Rombel yang Digabung <span class="required">*</span>
        </p>

        <p class="mb-3 text-xs text-slate-500">
            Pilih rombel mahasiswa yang masuk ke kelas gabungan ini.
            Contoh: GAB A berisi kelas A, B, dan C.
        </p>

        <div class="grid gap-2 rounded-2xl border border-slate-200 p-4 sm:grid-cols-4">
            @foreach ($studentGroups as $group)
                <label class="flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 text-sm">
                    <input
                        type="checkbox"
                        name="group_members[]"
                        value="{{ $group }}"
                        @checked(in_array((string) $group, $selectedGroupMembers, true))
                        class="group-member-checkbox rounded border-slate-300 text-indigo-600"
                    >
                    <span>Kelas {{ $group }}</span>
                </label>
            @endforeach
        </div>
    </div>

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
        Opsional. Mahasiswa utama otomatis dihitung dari semester mata kuliah dan rombel kelas.
        Checklist ini hanya dipakai untuk tambahan khusus.
    </p>

    <div id="students-wrapper" class="grid max-h-72 gap-2 overflow-y-auto rounded-2xl border border-slate-200 p-4 md:grid-cols-2">
        @foreach ($students as $student)
            <label
                class="student-option flex items-center gap-2 rounded-xl bg-slate-50 px-3 py-2 text-sm"
                data-semester-id="{{ $student->study_semester_id }}"
                data-student-group="{{ $student->student_group }}"
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
                        (
                            {{ $student->nim_nip ?? '-' }}
                            •
                            {{ $student->studySemester?->name ?? 'Tanpa Semester' }}
                            •
                            Kelas {{ $student->student_group ?? '-' }}
                        )
                    </span>
                </span>
            </label>
        @endforeach
    </div>

    <p id="student-filter-note" class="mt-3 text-xs text-slate-500">
        Pilih mata kuliah terlebih dahulu agar daftar mahasiswa manual disesuaikan dengan semester mata kuliah.
    </p>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const courseSelect = document.getElementById('course_id');
        const studentOptions = document.querySelectorAll('.student-option');
        const note = document.getElementById('student-filter-note');

        const classTypeSelect = document.getElementById('class_type');
        const regularGroupWrapper = document.getElementById('regular-group-wrapper');
        const combinedLabelWrapper = document.getElementById('combined-label-wrapper');
        const combinedMembersWrapper = document.getElementById('combined-members-wrapper');

        const studentGroupSelect = document.getElementById('student_group');
        const groupLabelInput = document.getElementById('group_label');
        const groupMemberCheckboxes = document.querySelectorAll('.group-member-checkbox');

        const nameInput = document.querySelector('input[name="name"]');

        let lastGeneratedName = '';

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
                note.textContent = 'Pilih mata kuliah terlebih dahulu agar daftar mahasiswa manual disesuaikan dengan semester mata kuliah.';
            } else if (visibleCount === 0) {
                note.textContent = 'Belum ada mahasiswa aktif pada semester mata kuliah ini.';
            } else {
                note.textContent = 'Daftar mahasiswa manual sudah difilter berdasarkan semester mata kuliah.';
            }
        }

        function setRequiredState() {
            const type = classTypeSelect.value;

            if (type === 'combined') {
                studentGroupSelect.removeAttribute('required');
                groupLabelInput.setAttribute('required', 'required');

                groupMemberCheckboxes.forEach(function (checkbox) {
                    checkbox.dataset.requiredGroup = '1';
                });
            } else {
                studentGroupSelect.setAttribute('required', 'required');
                groupLabelInput.removeAttribute('required');

                groupMemberCheckboxes.forEach(function (checkbox) {
                    checkbox.dataset.requiredGroup = '0';
                });
            }
        }

        function toggleClassTypeFields() {
            const type = classTypeSelect.value;

            if (type === 'combined') {
                regularGroupWrapper.style.display = 'none';
                combinedLabelWrapper.style.display = '';
                combinedMembersWrapper.style.display = '';
            } else {
                regularGroupWrapper.style.display = '';
                combinedLabelWrapper.style.display = 'none';
                combinedMembersWrapper.style.display = 'none';
            }

            setRequiredState();
            maybeGenerateClassName();
        }

        function maybeGenerateClassName() {
            if (!nameInput) {
                return;
            }

            const currentName = nameInput.value.trim();
            const canOverwrite = currentName === '' || currentName === lastGeneratedName;

            if (!canOverwrite) {
                return;
            }

            let generatedName = '';

            if (classTypeSelect.value === 'regular') {
                const selectedGroup = studentGroupSelect.value;

                if (selectedGroup) {
                    generatedName = 'Kelas ' + selectedGroup;
                }
            } else {
                const label = groupLabelInput.value.trim().toUpperCase();

                if (label) {
                    generatedName = label;
                }
            }

            if (generatedName !== '') {
                nameInput.value = generatedName;
                lastGeneratedName = generatedName;
            }
        }

        function validateCombinedMembers(event) {
            if (classTypeSelect.value !== 'combined') {
                return true;
            }

            const checkedCount = Array.from(groupMemberCheckboxes).filter(function (checkbox) {
                return checkbox.checked;
            }).length;

            if (checkedCount === 0) {
                event.preventDefault();
                alert('Pilih minimal satu rombel untuk kelas gabungan.');
                return false;
            }

            return true;
        }

        courseSelect.addEventListener('change', filterStudentsByCourseSemester);
        classTypeSelect.addEventListener('change', toggleClassTypeFields);
        studentGroupSelect.addEventListener('change', maybeGenerateClassName);
        groupLabelInput.addEventListener('input', function () {
            groupLabelInput.value = groupLabelInput.value.toUpperCase();
            maybeGenerateClassName();
        });

        const form = courseSelect.closest('form');
        if (form) {
            form.addEventListener('submit', validateCombinedMembers);
        }

        filterStudentsByCourseSemester();
        toggleClassTypeFields();
    });
</script>