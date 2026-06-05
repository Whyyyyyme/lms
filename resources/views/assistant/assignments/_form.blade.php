@php
    $selectedClass = $selectedClass ?? null;
@endphp

<div class="form-grid">
    @if($selectedClass)
        <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">

        <div class="alert" style="grid-column:1/-1;">
            <strong>Mata Kuliah:</strong>
            {{ $selectedClass->course?->name ?? 'Mata kuliah tidak tersedia' }}
            @if($selectedClass->course?->code)
                ({{ $selectedClass->course->code }})
            @endif
            · {{ $selectedClass->name }}
            @if($selectedClass->course?->studySemester)
                · {{ $selectedClass->course->studySemester->name }}
            @endif
        </div>
    @else
        @include('partials.form.select', ['label' => 'Kelas', 'name' => 'class_id', 'required' => true])
            <option value="">Pilih kelas praktikum</option>

            @foreach(($classes ?? collect()) as $class)
                @php
                    $courseName = $class->course?->name ?? 'Mata kuliah tidak tersedia';
                    $courseCode = $class->course?->code;
                    $semesterName = $class->course?->studySemester?->name;
                @endphp

                <option value="{{ $class->id }}" @selected((string) old('class_id', $assignment->class_id ?? '') === (string) $class->id)>
                    {{ $courseName }}
                    @if($courseCode)
                        ({{ $courseCode }})
                    @endif
                    - {{ $class->name }}
                    @if($semesterName)
                        - {{ $semesterName }}
                    @endif
                </option>
            @endforeach
        </select></label>
    @endif

    @include('partials.form.input', [
        'label' => 'Deadline',
        'name' => 'deadline',
        'type' => 'datetime-local',
        'value' => old('deadline', isset($assignment) && $assignment->deadline ? $assignment->deadline->timezone(config('app.timezone', 'Asia/Jakarta'))->format('Y-m-d\TH:i') : now(config('app.timezone', 'Asia/Jakarta'))->addWeek()->format('Y-m-d\TH:i')),
        'required' => true
    ])

    @include('partials.form.input', [
        'label' => 'Judul Tugas',
        'name' => 'title',
        'value' => old('title', $assignment->title ?? null),
        'required' => true
    ])

    @include('partials.form.input', [
        'label' => 'Nilai Maksimal',
        'name' => 'max_score',
        'type' => 'number',
        'value' => old('max_score', $assignment->max_score ?? 100),
        'required' => true
    ])

    <div style="grid-column:1/-1;">
        @include('partials.form.textarea', [
            'label' => 'Deskripsi',
            'name' => 'description',
            'value' => old('description', $assignment->description ?? null)
        ])
    </div>

    <div style="grid-column:1/-1;">
        @include('partials.form.input', [
            'label' => 'File Instruksi Tugas',
            'name' => 'file',
            'type' => 'file',
            'help' => 'PDF, DOCX, TXT, MD, atau CSV. Kosongkan saat edit jika tidak mengganti file.'
        ])
    </div>
</div>
