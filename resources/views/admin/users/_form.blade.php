@php
    $studySemesters = $studySemesters ?? collect();

    $selectedRole = old(
        'role',
        isset($user) ? ($user->roles->pluck('name')->first() ?: $user->role) : 'mahasiswa'
    );

    $studentGroups = $studentGroups ?? ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
    $selectedStudentGroup = old('student_group', $user->student_group ?? '');

    $isActiveChecked = old('is_active', isset($user) ? (bool) $user->is_active : true);
@endphp

<div class="form-grid">
    <div class="form-group">
        <label for="name" class="form-label">
            Nama Lengkap <span class="required">*</span>
        </label>

        <input
            id="name"
            type="text"
            name="name"
            class="form-control"
            value="{{ old('name', $user->name ?? null) }}"
            placeholder="Masukkan nama lengkap"
            required
        >

        @error('name')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="nim_nip" class="form-label">
            <span id="identifier-label">NIM Mahasiswa</span>
        </label>

        <input
            id="nim_nip"
            name="nim_nip"
            type="text"
            value="{{ old('nim_nip', $user->nim_nip ?? '') }}"
            class="form-control"
            placeholder="Masukkan NIM / NIP / kode asisten"
        >

        <div id="identifier-help" class="form-help">
            Isi NIM untuk mahasiswa.
        </div>

        @error('nim_nip')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="email" class="form-label">
            Email Aktif <span class="required">*</span>
        </label>

        <input
            id="email"
            type="email"
            name="email"
            class="form-control"
            value="{{ old('email', $user->email ?? null) }}"
            placeholder="contoh: nama@gmail.com"
            required
        >

        <div class="form-help">
            Gunakan email asli yang bisa dibuka oleh mahasiswa/asisten. Password email tidak dibutuhkan;
            password di bawah adalah password khusus untuk login LMS.
        </div>

        @error('email')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="role" class="form-label">
            Jenis Akun <span class="required">*</span>
        </label>

        <select id="role" name="role" required class="form-control">
            <option value="mahasiswa" @selected($selectedRole === 'mahasiswa')>
                Mahasiswa
            </option>

            <option value="asisten" @selected($selectedRole === 'asisten')>
                Asisten Praktikum
            </option>
        </select>

        <div class="form-help">
            Pilih apakah user ini mahasiswa atau asisten praktikum.
        </div>

        @error('role')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group" id="semester-wrapper">
        <label for="study_semester_id" class="form-label">
            Semester Mahasiswa <span class="required">*</span>
        </label>

        <select id="study_semester_id" name="study_semester_id" class="form-control">
            <option value="">Pilih semester mahasiswa</option>

            @foreach ($studySemesters as $semester)
                <option
                    value="{{ $semester->id }}"
                    @selected((string) old('study_semester_id', $user->study_semester_id ?? '') === (string) $semester->id)
                >
                    {{ $semester->name }}
                </option>
            @endforeach
        </select>

        <div class="form-help">
            Mahasiswa wajib masuk ke salah satu semester.
        </div>

        @error('study_semester_id')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group" id="student-group-wrapper">
        <label for="student_group" class="form-label">
            Kelas/Rombel Mahasiswa <span class="required">*</span>
        </label>

        <select id="student_group" name="student_group" class="form-control">
            <option value="">Pilih kelas/rombel</option>

            @foreach ($studentGroups as $group)
                <option value="{{ $group }}" @selected((string) $selectedStudentGroup === (string) $group)>
                    Kelas {{ $group }}
                </option>
            @endforeach
        </select>

        <div class="form-help">
            Pilih rombel mahasiswa sesuai kelas perkuliahan, misalnya A, B, C, sampai H.
        </div>

        @error('student_group')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="password" class="form-label">
            {{ isset($user) ? 'Password LMS Baru' : 'Password LMS' }}
            @if(! isset($user))
                <span class="required">*</span>
            @endif
        </label>

        <input
            id="password"
            type="password"
            name="password"
            class="form-control"
            placeholder="{{ isset($user) ? 'Kosongkan jika tidak ingin mengganti password' : 'Masukkan password LMS' }}"
            @required(! isset($user))
        >

        <div class="form-help">
            @if(isset($user))
                Kosongkan jika tidak ingin mengganti password LMS user ini.
            @else
                Password ini khusus untuk login ke LMS, bukan password Gmail/email pengguna.
            @endif
        </div>

        @error('password')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="password_confirmation" class="form-label">
            Konfirmasi Password LMS
            @if(! isset($user))
                <span class="required">*</span>
            @endif
        </label>

        <input
            id="password_confirmation"
            type="password"
            name="password_confirmation"
            class="form-control"
            placeholder="Ulangi password LMS"
            @required(! isset($user))
        >

        @error('password_confirmation')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group" style="grid-column: 1 / -1;">
        <label class="form-label">
            Status Akun
        </label>

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
                User aktif
            </span>
        </label>

        <div class="form-help">
            Jika dinonaktifkan, user tidak dapat digunakan sebagai akun aktif di sistem.
        </div>

        @error('is_active')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>
</div>

<div id="student-note" class="alert" style="margin-top: 16px;">
    <strong>Catatan mahasiswa:</strong>
    Form mahasiswa membutuhkan semester dan kelas/rombel. Mata kuliah dan kelas praktikum yang muncul untuk mahasiswa akan mengikuti semester serta rombel tersebut.
</div>

<div id="assistant-note" class="alert" style="margin-top: 16px;">
    <strong>Catatan asisten:</strong>
    Form asisten tidak membutuhkan semester dan kelas/rombel. Asisten akan dihubungkan ke kelas praktikum melalui fitur kelola kelas.
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
            if (! roleSelect) {
                return;
            }

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