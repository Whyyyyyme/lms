<div class="space-y-5">
@include('partials.form.select', ['label' => 'Kelas', 'name' => 'class_id', 'required' => true])@foreach($classes as $class)<option value="{{ $class->id }}" @selected((string) old('class_id', $announcement->class_id ?? '') === (string) $class->id)>{{ $class->course?->name }} - {{ $class->name }}</option>@endforeach</select>
@include('partials.form.input', ['label' => 'Judul', 'name' => 'title', 'value' => $announcement->title ?? null, 'required' => true])
@include('partials.form.textarea', ['label' => 'Isi Pengumuman', 'name' => 'content', 'value' => $announcement->content ?? null, 'required' => true])
</div>
