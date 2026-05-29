<div class="grid gap-5 md:grid-cols-2">
    @include('partials.form.input', ['label' => 'Nama Lengkap', 'name' => 'name', 'value' => $user->name ?? null, 'required' => true])
    @include('partials.form.input', ['label' => 'NIM / NIP', 'name' => 'nim_nip', 'value' => $user->nim_nip ?? null])
    @include('partials.form.input', ['label' => 'Email', 'name' => 'email', 'type' => 'email', 'value' => $user->email ?? null, 'required' => true])

    @include('partials.form.select', ['label' => 'Role', 'name' => 'role', 'required' => true])
        @php($selectedRole = old('role', isset($user) ? $user->roles->pluck('name')->first() : 'mahasiswa'))
        @foreach ($roles as $role)
            <option value="{{ $role }}" @selected($selectedRole === $role)>{{ ucfirst($role) }}</option>
        @endforeach
    </select>

    @include('partials.form.select', ['label' => 'Semester Mahasiswa', 'name' => 'study_semester_id'])
        <option value="">Tidak ada / bukan mahasiswa</option>
        @foreach ($studySemesters as $semester)
            <option value="{{ $semester->id }}" @selected((string) old('study_semester_id', $user->study_semester_id ?? '') === (string) $semester->id)>
                {{ $semester->name }}
            </option>
        @endforeach
    </select>

    @include('partials.form.input', ['label' => isset($user) ? 'Password Baru' : 'Password', 'name' => 'password', 'type' => 'password', 'required' => !isset($user)])
    @include('partials.form.input', ['label' => 'Konfirmasi Password', 'name' => 'password_confirmation', 'type' => 'password', 'required' => !isset($user)])
</div>

<p class="mt-3 rounded-xl bg-blue-50 px-4 py-3 text-sm text-blue-700">
    Mahasiswa sekarang diklasifikasikan berdasarkan semester. Matakuliah yang muncul untuk mahasiswa akan mengikuti semester tersebut, bukan hanya satu kelas/matakuliah.
</p>

<div class="mt-5">
    @include('partials.form.checkbox', ['label' => 'User aktif', 'name' => 'is_active', 'checked' => $user->is_active ?? true])
</div>
