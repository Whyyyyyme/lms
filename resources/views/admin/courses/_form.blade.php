<div class="grid gap-5 md:grid-cols-2">
    @include('partials.form.select', ['label' => 'Tahun Akademik', 'name' => 'academic_year_id', 'required' => true])
        @foreach ($academicYears as $year)
            <option value="{{ $year->id }}" @selected((string) old('academic_year_id', $course->academic_year_id ?? '') === (string) $year->id)>{{ $year->year }} - {{ ucfirst($year->semester) }}</option>
        @endforeach
    </select>
    @include('partials.form.input', ['label' => 'Nama Matakuliah', 'name' => 'name', 'value' => $course->name ?? null, 'required' => true])
    @include('partials.form.input', ['label' => 'Kode', 'name' => 'code', 'value' => $course->code ?? null, 'required' => true])
    @include('partials.form.input', ['label' => 'SKS', 'name' => 'sks', 'type' => 'number', 'value' => $course->sks ?? 1, 'required' => true])
</div>
<div class="mt-5">@include('partials.form.checkbox', ['label' => 'Matakuliah aktif', 'name' => 'is_active', 'checked' => $course->is_active ?? true])</div>
