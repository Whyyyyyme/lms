@php
    $timezone = config('app.timezone', 'Asia/Jakarta');

    $deadlineValue = old(
        'deadline',
        isset($assignment) && $assignment->deadline
            ? $assignment->deadline->timezone($timezone)->format('Y-m-d\TH:i')
            : now($timezone)->addWeek()->format('Y-m-d\TH:i')
    );

    $hasExistingFile = isset($assignment) && filled($assignment->file_path);
@endphp

<div class="form-grid">
    @include('partials.form.select', [
        'label' => 'Kelas Praktikum',
        'name' => 'class_id',
        'required' => true
    ])
        <option value="">Pilih kelas praktikum</option>

        @foreach(($classes ?? collect()) as $class)
            @php
                $courseName = $class->course?->name ?? 'Mata kuliah tidak tersedia';
                $courseCode = $class->course?->code;
            @endphp

            <option
                value="{{ $class->id }}"
                @selected((string) old('class_id', $assignment->class_id ?? '') === (string) $class->id)
            >
                {{ $courseName }}
                @if($courseCode)
                    ({{ $courseCode }})
                @endif
                - {{ $class->name }}
            </option>
        @endforeach
    </select></label>

    @include('partials.form.input', [
        'label' => 'Deadline',
        'name' => 'deadline',
        'type' => 'datetime-local',
        'value' => $deadlineValue,
        'required' => true,
        'help' => 'Mahasiswa tidak bisa mengumpulkan tugas setelah melewati waktu ini.'
    ])

    @include('partials.form.input', [
        'label' => 'Judul Tugas',
        'name' => 'title',
        'value' => old('title', $assignment->title ?? null),
        'required' => true,
        'placeholder' => 'Contoh: Quiz Optimasi Pertemuan 1'
    ])

    @include('partials.form.input', [
        'label' => 'Nilai Maksimal',
        'name' => 'max_score',
        'type' => 'number',
        'value' => old('max_score', $assignment->max_score ?? 100),
        'required' => true,
        'help' => 'Contoh: 100'
    ])

    <div style="grid-column:1/-1;">
        @include('partials.form.textarea', [
            'label' => 'Deskripsi',
            'name' => 'description',
            'value' => old('description', $assignment->description ?? null)
        ])
    </div>

    <div style="grid-column:1/-1;">
        @if($hasExistingFile)
            <div class="alert" style="margin-bottom:12px;">
                File tugas saat ini sudah tersimpan.
                Kosongkan upload file jika tidak ingin mengganti file tugas.
            </div>
        @endif

        <label class="form-label" for="file">
            File Instruksi Tugas
        </label>

        <input
            id="file"
            class="form-control @error('file') is-invalid @enderror"
            type="file"
            name="file"
            accept=".pdf,.docx,.txt,.md,.csv,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,text/plain,text/markdown,text/csv"
        >

        <p class="form-help">
            Format yang didukung: PDF, DOCX, TXT, MD, atau CSV. Maksimal 100 MB.
            Hindari PPT, PPTX, ZIP, RAR, DOC lama, atau file hasil scan/gambar jika ingin isi tugas bisa dibaca oleh AI.
        </p>

        @error('file')
            <div class="form-error">
                {{ $message }}
            </div>
        @enderror
    </div>
</div>