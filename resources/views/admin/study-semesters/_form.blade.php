<div class="grid gap-5 md:grid-cols-2">
    @include('partials.form.input', ['label' => 'Level Semester', 'name' => 'level', 'type' => 'number', 'value' => $studySemester->level ?? null, 'required' => true])
    @include('partials.form.input', ['label' => 'Nama Semester', 'name' => 'name', 'value' => $studySemester->name ?? null, 'placeholder' => 'Semester 1', 'required' => true])
    <div class="md:col-span-2">
        @include('partials.form.textarea', ['label' => 'Deskripsi', 'name' => 'description', 'value' => $studySemester->description ?? null])
    </div>
</div>
<div class="mt-5">
    @include('partials.form.checkbox', ['label' => 'Semester aktif', 'name' => 'is_active', 'checked' => $studySemester->is_active ?? true])
</div>
