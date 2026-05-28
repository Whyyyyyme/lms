<div class="grid gap-5 md:grid-cols-2">
    @include('partials.form.input', ['label' => 'Tahun Akademik', 'name' => 'year', 'value' => $academicYear->year ?? null, 'required' => true, 'placeholder' => '2025/2026'])
    @include('partials.form.select', ['label' => 'Semester', 'name' => 'semester', 'required' => true])
        @foreach (['ganjil' => 'Ganjil', 'genap' => 'Genap'] as $value => $label)
            <option value="{{ $value }}" @selected(old('semester', $academicYear->semester ?? 'ganjil') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</div>
<div class="mt-5">@include('partials.form.checkbox', ['label' => 'Jadikan semester aktif', 'name' => 'is_active', 'checked' => $academicYear->is_active ?? false])</div>
