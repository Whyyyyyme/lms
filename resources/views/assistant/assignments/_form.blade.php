<div class="grid gap-5 md:grid-cols-2">
    @include('partials.form.select', ['label' => 'Kelas', 'name' => 'class_id', 'required' => true])
        @foreach ($classes as $class)
            <option value="{{ $class->id }}" @selected((string) old('class_id', $assignment->class_id ?? '') === (string) $class->id)>{{ $class->course?->name }} - {{ $class->name }}</option>
        @endforeach
    </select>
    @include('partials.form.input', ['label' => 'Deadline', 'name' => 'deadline', 'type' => 'datetime-local', 'value' => isset($assignment) && $assignment->deadline ? $assignment->deadline->format('Y-m-d\TH:i') : now()->addWeek()->format('Y-m-d\TH:i'), 'required' => true])
    @include('partials.form.input', ['label' => 'Judul Tugas', 'name' => 'title', 'value' => $assignment->title ?? null, 'required' => true])
    @include('partials.form.input', ['label' => 'Nilai Maksimal', 'name' => 'max_score', 'type' => 'number', 'value' => $assignment->max_score ?? 100, 'required' => true])
    <div class="md:col-span-2">@include('partials.form.textarea', ['label' => 'Deskripsi', 'name' => 'description', 'value' => $assignment->description ?? null])</div>
    <div class="md:col-span-2">@include('partials.form.input', ['label' => 'File Instruksi Tugas', 'name' => 'file', 'type' => 'file'])</div>
</div>
