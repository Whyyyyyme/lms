<div class="grid grid-2">
    @include('partials.form.input', [
        'label' => 'Level Semester',
        'name' => 'level',
        'type' => 'number',
        'value' => $studySemester->level ?? null,
        'placeholder' => 'Contoh: 1',
        'required' => true,
        'help' => 'Isi angka semester, misalnya 1 sampai 8.'
    ])

    @include('partials.form.input', [
        'label' => 'Nama Semester',
        'name' => 'name',
        'value' => $studySemester->name ?? null,
        'placeholder' => 'Contoh: Semester 1',
        'required' => true,
        'help' => 'Nama yang akan tampil di form mahasiswa dan mata kuliah.'
    ])

    <div style="grid-column:1 / -1;">
        @include('partials.form.textarea', [
            'label' => 'Deskripsi',
            'name' => 'description',
            'value' => $studySemester->description ?? null,
            'placeholder' => 'Contoh: Semester mahasiswa tingkat 1'
        ])
    </div>
</div>

<div class="mt-5">
    @include('partials.form.checkbox', [
        'label' => 'Semester aktif',
        'name' => 'is_active',
        'checked' => $studySemester->is_active ?? true
    ])
</div>

<p style="margin-top:12px; color:#64748b; font-size:14px;">
    Catatan: semester nonaktif tidak akan muncul di form tambah mahasiswa dan mata kuliah baru.
</p>