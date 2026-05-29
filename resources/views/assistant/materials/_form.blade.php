<div class="form-grid">
    @include('partials.form.select', ['label' => 'Kelas', 'name' => 'class_id', 'required' => true])
        @foreach(($classes ?? collect()) as $class)
            <option value="{{ $class->id }}" @selected((string) old('class_id', $material->class_id ?? '') === (string) $class->id)>{{ $class->course?->name }} - {{ $class->name }}</option>
        @endforeach
    </select></label>

    @include('partials.form.select', ['label' => 'Tipe Materi', 'name' => 'type', 'required' => true])
        @foreach(['pdf' => 'PDF', 'video' => 'Video Link', 'dokumen' => 'Dokumen', 'link' => 'Link'] as $value => $label)
            <option value="{{ $value }}" @selected(old('type', $material->type ?? 'pdf') === $value)>{{ $label }}</option>
        @endforeach
    </select></label>

    <div style="grid-column:1/-1;">@include('partials.form.input', ['label' => 'Judul Materi', 'name' => 'title', 'value' => $material->title ?? null, 'required' => true])</div>
    <div style="grid-column:1/-1;">@include('partials.form.textarea', ['label' => 'Deskripsi', 'name' => 'description', 'value' => $material->description ?? null])</div>
    @include('partials.form.input', ['label' => 'Upload File', 'name' => 'file', 'type' => 'file', 'help' => 'PDF, DOC, DOCX, PPT, PPTX, ZIP, atau RAR. Kosongkan saat edit jika tidak mengganti file.'])
    @include('partials.form.input', ['label' => 'Link Video / Materi', 'name' => 'link', 'type' => 'url', 'value' => isset($material) && str_starts_with((string) $material->file_path, 'http') ? $material->file_path : null, 'placeholder' => 'https://...'])
    @include('partials.form.input', ['label' => 'Tanggal Publikasi', 'name' => 'published_at', 'type' => 'datetime-local', 'value' => isset($material) && $material->published_at ? $material->published_at->format('Y-m-d\TH:i') : now()->format('Y-m-d\TH:i')])
</div>
