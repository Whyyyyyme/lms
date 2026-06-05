@php
    $courses = $courses ?? collect();
    $assistants = $assistants ?? collect();
    $students = $students ?? collect();

    $classTypes = $classTypes ?? [
        'regular' => 'Reguler',
        'combined' => 'Gabungan',
    ];

    $studentGroups = $studentGroups ?? ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

    $selectedStudents = collect(
        old('student_ids', isset($praktikumClass) ? $praktikumClass->students->pluck('id')->all() : [])
    )->map(fn ($id) => (string) $id)->all();

    $selectedCourseId = old('course_id', $praktikumClass->course_id ?? '');

    $selectedAssistantId = old('assistant_id', $praktikumClass->assistant_id ?? '');

    $selectedClassType = old('class_type', $praktikumClass->class_type ?? 'regular');

    $selectedStudentGroup = old('student_group', $praktikumClass->student_group ?? '');

    $selectedGroupLabel = old('group_label', $praktikumClass->group_label ?? '');

    $selectedGroupMembers = collect(
        old('group_members', isset($praktikumClass) ? ($praktikumClass->group_members ?? []) : [])
    )->map(fn ($group) => (string) $group)->all();

    $isActiveChecked = old(
        'is_active',
        isset($praktikumClass) ? (bool) $praktikumClass->is_active : true
    );
@endphp

<div class="form-grid">
    <div class="form-group">
        <label for="course_id" class="form-label">
            Mata Kuliah <span class="required">*</span>
        </label>

        <select
            id="course_id"
            name="course_id"
            class="form-control"
            required
        >
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

        <div class="form-help">
            Kelas akan mengikuti semester dari mata kuliah yang dipilih.
        </div>

        @error('course_id')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="assistant_id" class="form-label">
            Asisten Praktikum
        </label>

        <select
            id="assistant_id"
            name="assistant_id"
            class="form-control"
        >
            <option value="">Belum ditentukan</option>

            @foreach ($assistants as $assistant)
                <option
                    value="{{ $assistant->id }}"
                    @selected((string) $selectedAssistantId === (string) $assistant->id)
                >
                    {{ $assistant->name }}

                    @if($assistant->nim_nip)
                        - {{ $assistant->nim_nip }}
                    @endif
                </option>
            @endforeach
        </select>

        <div class="form-help">
            Asisten dapat diganti kapan saja.
        </div>

        @error('assistant_id')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="class_type" class="form-label">
            Tipe Kelas <span class="required">*</span>
        </label>

        <select
            id="class_type"
            name="class_type"
            class="form-control"
            required
        >
            @foreach ($classTypes as $value => $label)
                <option value="{{ $value }}" @selected($selectedClassType === $value)>
                    {{ $label }}
                </option>
            @endforeach
        </select>

        <div class="form-help">
            Pilih Reguler untuk kelas A-H, atau Gabungan untuk kelas seperti GAB A.
        </div>

        @error('class_type')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group" id="regular-group-wrapper">
        <label for="student_group" class="form-label">
            Rombel Reguler <span class="required">*</span>
        </label>

        <select
            id="student_group"
            name="student_group"
            class="form-control"
        >
            <option value="">Pilih rombel</option>

            @foreach ($studentGroups as $group)
                <option value="{{ $group }}" @selected($selectedStudentGroup === $group)>
                    Kelas {{ $group }}
                </option>
            @endforeach
        </select>

        <div class="form-help">
            Mahasiswa otomatis diambil dari semester mata kuliah dan rombel ini.
        </div>

        @error('student_group')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group" id="combined-label-wrapper">
        <label for="group_label" class="form-label">
            Label Kelas Gabungan <span class="required">*</span>
        </label>

        <input
            id="group_label"
            type="text"
            name="group_label"
            value="{{ $selectedGroupLabel }}"
            class="form-control"
            placeholder="Contoh: GAB A"
        >

        <div class="form-help">
            Contoh: GAB A, GAB B, atau Gabungan 1.
        </div>

        @error('group_label')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="name" class="form-label">
            Nama Kelas <span class="required">*</span>
        </label>

        <input
            id="name"
            type="text"
            name="name"
            class="form-control"
            value="{{ old('name', $praktikumClass->name ?? null) }}"
            placeholder="Contoh: Kelas E atau GAB A"
            required
        >

        <div class="form-help">
            Nama kelas akan otomatis terisi dari rombel, tetapi tetap bisa diedit manual.
        </div>

        @error('name')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="room" class="form-label">
            Ruangan
        </label>

        <input
            id="room"
            type="text"
            name="room"
            class="form-control"
            value="{{ old('room', $praktikumClass->room ?? null) }}"
            placeholder="Contoh: Lab Komputer 1"
        >

        <div class="form-help">
            Isi ruangan praktikum jika sudah tersedia.
        </div>

        @error('room')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group" style="grid-column: 1 / -1;" id="combined-members-wrapper">
        <label class="form-label">
            Rombel yang Digabung <span class="required">*</span>
        </label>

        <div class="form-help" style="margin-bottom: 10px;">
            Pilih rombel mahasiswa yang masuk ke kelas gabungan ini.
            Contoh: GAB A berisi kelas A, B, dan C.
        </div>

        <div
            style="
                display: grid;
                grid-template-columns: repeat(4, minmax(0, 1fr));
                gap: 10px;
                padding: 14px;
                border: 1px solid var(--line);
                border-radius: 18px;
                background: #f8fafc;
            "
        >
            @foreach ($studentGroups as $group)
                <label
                    style="
                        display: flex;
                        align-items: center;
                        gap: 10px;
                        padding: 10px 12px;
                        border: 1px solid var(--line);
                        border-radius: 14px;
                        background: #ffffff;
                        cursor: pointer;
                        font-size: 14px;
                        font-weight: 700;
                        color: #334155;
                    "
                >
                    <input
                        type="checkbox"
                        name="group_members[]"
                        value="{{ $group }}"
                        @checked(in_array((string) $group, $selectedGroupMembers, true))
                        class="group-member-checkbox"
                        style="width: 16px; height: 16px;"
                    >

                    <span>Kelas {{ $group }}</span>
                </label>
            @endforeach
        </div>

        @error('group_members')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group" style="grid-column: 1 / -1;">
        <label for="schedule" class="form-label">
            Jadwal
        </label>

        <input
            id="schedule"
            type="text"
            name="schedule"
            class="form-control"
            value="{{ old('schedule', $praktikumClass->schedule ?? null) }}"
            placeholder="Contoh: Senin, 10.00 - 12.00"
        >

        <div class="form-help">
            Isi jadwal praktikum. Format yang disarankan: hari, jam mulai - jam selesai.
        </div>

        @error('schedule')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group" style="grid-column: 1 / -1;">
        <label class="form-label">
            Status Kelas
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
                Kelas aktif
            </span>
        </label>

        <div class="form-help">
            Kelas aktif dapat digunakan oleh asisten dan mahasiswa sesuai aturan akses.
        </div>

        @error('is_active')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>
</div>

<section class="card" style="margin-top: 18px; box-shadow: none;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Mahasiswa Manual / Khusus</h2>
            <div class="section-subtitle">
                Opsional. Mahasiswa utama otomatis dihitung dari semester mata kuliah dan rombel kelas.
                Checklist ini hanya dipakai untuk tambahan khusus.
            </div>
        </div>
    </div>

    @if($students->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">🎓</div>

            <h3 class="empty-state-title">
                Belum ada mahasiswa
            </h3>

            <p class="empty-state-text">
                Mahasiswa manual akan tampil setelah ada akun mahasiswa aktif di sistem.
            </p>
        </div>
    @else
        <div
            id="students-wrapper"
            style="
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 10px;
                max-height: 320px;
                overflow-y: auto;
                padding: 14px;
                border: 1px solid var(--line);
                border-radius: 18px;
                background: #f8fafc;
            "
        >
            @foreach ($students as $student)
                <label
                    class="student-option"
                    data-semester-id="{{ $student->study_semester_id }}"
                    data-student-group="{{ $student->student_group }}"
                    style="
                        display: flex;
                        align-items: flex-start;
                        gap: 10px;
                        padding: 10px 12px;
                        border: 1px solid var(--line);
                        border-radius: 14px;
                        background: #ffffff;
                        cursor: pointer;
                        font-size: 14px;
                        color: #334155;
                    "
                >
                    <input
                        type="checkbox"
                        name="student_ids[]"
                        value="{{ $student->id }}"
                        @checked(in_array((string) $student->id, $selectedStudents, true))
                        style="width: 16px; height: 16px; margin-top: 2px;"
                    >

                    <span>
                        <strong>{{ $student->name }}</strong>

                        <span class="item-meta" style="display: block; margin-top: 3px;">
                            {{ $student->nim_nip ?? '-' }}
                            •
                            {{ $student->studySemester?->name ?? 'Tanpa Semester' }}
                            •
                            Kelas {{ $student->student_group ?? '-' }}
                        </span>
                    </span>
                </label>
            @endforeach
        </div>

        <div id="student-filter-note" class="form-help" style="margin-top: 12px;">
            Pilih mata kuliah terlebih dahulu agar daftar mahasiswa manual disesuaikan dengan semester mata kuliah.
        </div>
    @endif
</section>

<div class="alert" style="margin-top: 16px;">
    <strong>Catatan:</strong>
    Untuk kelas reguler, mahasiswa otomatis diambil dari semester mata kuliah dan rombel.
    Untuk kelas gabungan, mahasiswa otomatis diambil dari semester mata kuliah dan rombel yang digabung.
</div>

<style>
    @media (max-width: 900px) {
        #combined-members-wrapper > div,
        #students-wrapper {
            grid-template-columns: 1fr !important;
        }
    }
</style>

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
            if (!courseSelect) {
                return;
            }

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

                    if (checkbox) {
                        checkbox.checked = false;
                    }
                }
            });

            if (!note) {
                return;
            }

            if (!selectedSemesterId) {
                note.textContent = 'Pilih mata kuliah terlebih dahulu agar daftar mahasiswa manual disesuaikan dengan semester mata kuliah.';
            } else if (visibleCount === 0) {
                note.textContent = 'Belum ada mahasiswa aktif pada semester mata kuliah ini.';
            } else {
                note.textContent = 'Daftar mahasiswa manual sudah difilter berdasarkan semester mata kuliah.';
            }
        }

        function setRequiredState() {
            if (!classTypeSelect || !studentGroupSelect || !groupLabelInput) {
                return;
            }

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
            if (!classTypeSelect) {
                return;
            }

            const type = classTypeSelect.value;

            if (type === 'combined') {
                if (regularGroupWrapper) {
                    regularGroupWrapper.style.display = 'none';
                }

                if (combinedLabelWrapper) {
                    combinedLabelWrapper.style.display = '';
                }

                if (combinedMembersWrapper) {
                    combinedMembersWrapper.style.display = '';
                }
            } else {
                if (regularGroupWrapper) {
                    regularGroupWrapper.style.display = '';
                }

                if (combinedLabelWrapper) {
                    combinedLabelWrapper.style.display = 'none';
                }

                if (combinedMembersWrapper) {
                    combinedMembersWrapper.style.display = 'none';
                }
            }

            setRequiredState();
            maybeGenerateClassName();
        }

        function maybeGenerateClassName() {
            if (!nameInput || !classTypeSelect) {
                return;
            }

            const currentName = nameInput.value.trim();
            const canOverwrite = currentName === '' || currentName === lastGeneratedName;

            if (!canOverwrite) {
                return;
            }

            let generatedName = '';

            if (classTypeSelect.value === 'regular') {
                const selectedGroup = studentGroupSelect ? studentGroupSelect.value : '';

                if (selectedGroup) {
                    generatedName = 'Kelas ' + selectedGroup;
                }
            } else {
                const label = groupLabelInput ? groupLabelInput.value.trim().toUpperCase() : '';

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
            if (!classTypeSelect || classTypeSelect.value !== 'combined') {
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

        if (courseSelect) {
            courseSelect.addEventListener('change', filterStudentsByCourseSemester);
        }

        if (classTypeSelect) {
            classTypeSelect.addEventListener('change', toggleClassTypeFields);
        }

        if (studentGroupSelect) {
            studentGroupSelect.addEventListener('change', maybeGenerateClassName);
        }

        if (groupLabelInput) {
            groupLabelInput.addEventListener('input', function () {
                groupLabelInput.value = groupLabelInput.value.toUpperCase();
                maybeGenerateClassName();
            });
        }

        const form = courseSelect ? courseSelect.closest('form') : null;

        if (form) {
            form.addEventListener('submit', validateCombinedMembers);
        }

        filterStudentsByCourseSemester();
        toggleClassTypeFields();
    });
</script>