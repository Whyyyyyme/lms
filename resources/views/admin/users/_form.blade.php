@php
    $selectedRole = old('role', isset($user) ? ($user->roles->pluck('name')->first() ?: $user->role) : 'mahasiswa');

    $studentGroups = $studentGroups ?? ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

    $selectedStudentGroup = old('student_group', $user->student_group ?? '');
@endphp

<div class="grid gap-5 md:grid-cols-2">
    @include('partials.form.input', [
        'label' => 'Nama Lengkap',
        'name' => 'name',
        'value' => $user->name ?? null,
        'required' => true
    ])

    <label class="form-group" for="nim_nip">
        <span class="form-label">
            <span id="identifier-label">NIM Mahasiswa</span>
        </span>

        <input
            id="nim_nip"
            name="nim_nip"
            type="text"
            value="{{ old('nim_nip', $user->nim_nip ?? '') }}"
            class="form-control"
        >

        <small id="identifier-help" class="form-help">
            Isi NIM untuk mahasiswa.
        </small>
    </label>

    @include('partials.form.input', [
        'label' => 'Email Aktif',
        'name' => 'email',
        'type' => 'email',
        'value' => $user->email ?? null,
        'required' => true,
        'placeholder' => 'contoh: nama@gmail.com',
        'help' => 'Gunakan email asli yang bisa dibuka oleh mahasiswa/asisten. Password email tidak dibutuhkan; password di bawah adalah password khusus untuk login LMS.'
    ])

    <label class="form-group" for="role">
        <span class="form-label">Jenis Akun <span class="required">*</span></span>

        <select id="role" name="role" required class="form-control">
            <option value="mahasiswa" @selected($selectedRole === 'mahasiswa')>
                Mahasiswa
            </option>
            <option value="asisten" @selected($selectedRole === 'asisten')>
                Asisten Praktikum
            </option>
        </select>
    </label>

    <label class="form-group" for="study_semester_id" id="semester-wrapper">
        <span class="form-label">Semester Mahasiswa <span class="required">*</span></span>

        <select id="study_semester_id" name="study_semester_id" class="form-control">
            <option value="">Pilih semester mahasiswa</option>
            @foreach ($studySemesters as $semester)
                <option value="{{ $semester->id }}" @selected((string) old('study_semester_id', $user->study_semester_id ?? '') === (string) $semester->id)>
                    {{ $semester->name }}
                </option>
            @endforeach
        </select>

        <small class="form-help">
            Mahasiswa wajib masuk ke salah satu semester.
        </small>
    </label>

    <label class="form-group" for="student_group" id="student-group-wrapper">
        <span class="form-label">Kelas/Rombel Mahasiswa <span class="required">*</span></span>

        <select id="student_group" name="student_group" class="form-control">
            <option value="">Pilih kelas/rombel</option>
            @foreach ($studentGroups as $group)
                <option value="{{ $group }}" @selected((string) $selectedStudentGroup === (string) $group)>
                    Kelas {{ $group }}
                </option>
            @endforeach
        </select>

        <small class="form-help">
            Pilih rombel mahasiswa sesuai kelas perkuliahan, misalnya A, B, C, sampai H.
        </small>
    </label>

    @include('partials.form.input', [
        'label' => isset($user) ? 'Password LMS Baru' : 'Password LMS',
        'name' => 'password',
        'type' => 'password',
        'required' => !isset($user),
        'help' => isset($user)
            ? 'Kosongkan jika tidak ingin mengganti password LMS user ini.'
            : 'Password ini khusus untuk login ke LMS, bukan password Gmail/email pengguna.'
    ])

    @include('partials.form.input', [
        'label' => 'Konfirmasi Password LMS',
        'name' => 'password_confirmation',
        'type' => 'password',
        'required' => !isset($user)
    ])
</div>

<p id="student-note" class="mt-3 rounded-xl bg-blue-50 px-4 py-3 text-sm text-blue-700">
    Form mahasiswa membutuhkan semester dan kelas/rombel. Mata kuliah dan kelas praktikum yang muncul untuk mahasiswa akan mengikuti semester serta rombel tersebut.
</p>

<p id="assistant-note" class="mt-3 rounded-xl bg-amber-50 px-4 py-3 text-sm text-amber-700">
    Form asisten tidak membutuhkan semester dan kelas/rombel. Asisten akan dihubungkan ke kelas praktikum melalui fitur kelola kelas.
</p>

<div class="mt-5">
    @include('partials.form.checkbox', [
        'label' => 'User aktif',
        'name' => 'is_active',
        'checked' => $user->is_active ?? true
    ])
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleSelect = document.getElementById('role');

        const semesterWrapper = document.getElementById('semester-wrapper');
        const semesterSelect = document.getElementById('study_semester_id');

        const studentGroupWrapper = document.getElementById('student-group-wrapper');
        const studentGroupSelect = document.getElementById('student_group');

        const studentNote = document.getElementById('student-note');
        const assistantNote = document.getElementById('assistant-note');

        const identifierLabel = document.getElementById('identifier-label');
        const identifierHelp = document.getElementById('identifier-help');

        function toggleFormByRole() {
            if (roleSelect.value === 'mahasiswa') {
                semesterWrapper.style.display = '';
                semesterSelect.setAttribute('required', 'required');

                studentGroupWrapper.style.display = '';
                studentGroupSelect.setAttribute('required', 'required');

                studentNote.style.display = '';
                assistantNote.style.display = 'none';

                identifierLabel.textContent = 'NIM Mahasiswa';
                identifierHelp.textContent = 'Isi NIM untuk mahasiswa.';
            } else {
                semesterWrapper.style.display = 'none';
                semesterSelect.removeAttribute('required');
                semesterSelect.value = '';

                studentGroupWrapper.style.display = 'none';
                studentGroupSelect.removeAttribute('required');
                studentGroupSelect.value = '';

                studentNote.style.display = 'none';
                assistantNote.style.display = '';

                identifierLabel.textContent = 'NIP / Kode Asisten';
                identifierHelp.textContent = 'Isi NIP, kode asisten, atau identitas asisten.';
            }
        }

        roleSelect.addEventListener('change', toggleFormByRole);
        toggleFormByRole();
    });
</script>