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

    @include('partials.form.select', ['label' => 'Kelas Utama Mahasiswa', 'name' => 'kelas_id'])
        <option value="">Tidak ada</option>
        @foreach ($classes as $class)
            <option value="{{ $class->id }}" @selected((string) old('kelas_id', $user->kelas_id ?? '') === (string) $class->id)>
                {{ $class->course?->name }} - {{ $class->name }}
            </option>
        @endforeach
    </select>

    @include('partials.form.input', ['label' => isset($user) ? 'Password Baru' : 'Password', 'name' => 'password', 'type' => 'password', 'required' => !isset($user)])
    @include('partials.form.input', ['label' => 'Konfirmasi Password', 'name' => 'password_confirmation', 'type' => 'password', 'required' => !isset($user)])
</div>
<div class="mt-5">
    @include('partials.form.checkbox', ['label' => 'User aktif', 'name' => 'is_active', 'checked' => $user->is_active ?? true])
</div>
