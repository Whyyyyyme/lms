<div class="form-grid">
    @include('partials.form.input', ['label' => 'Tahun Akademik', 'name' => 'year', 'value' => $academicYear->year ?? null, 'placeholder' => '2025/2026', 'required' => true])
    @include('partials.form.select', ['label' => 'Semester', 'name' => 'semester', 'required' => true])
        @foreach(['ganjil' => 'Ganjil', 'genap' => 'Genap'] as $value => $label)
            <option value="{{ $value }}" @selected(old('semester', $academicYear->semester ?? 'ganjil') === $value)>{{ $label }}</option>
        @endforeach
    </select></label>
</div>
@include('partials.form.checkbox', ['label' => 'Jadikan tahun akademik aktif', 'name' => 'is_active', 'checked' => $academicYear->is_active ?? false])
