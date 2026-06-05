@php
    $selectedClass = $selectedClass ?? null;
    $classes = $classes ?? collect();

    $deadlineValue = old(
        'deadline',
        isset($assignment) && $assignment->deadline
            ? $assignment->deadline->timezone(config('app.timezone', 'Asia/Jakarta'))->format('Y-m-d\TH:i')
            : now(config('app.timezone', 'Asia/Jakarta'))->addWeek()->format('Y-m-d\TH:i')
    );

    $currentFilePath = (string) ($assignment->file_path ?? '');
    $hasExistingFile = isset($assignment) && $currentFilePath !== '';
@endphp

<div class="form-grid">
    @if($selectedClass)
        <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">

        <div class="alert" style="grid-column: 1 / -1; margin-bottom: 0;">
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
        <div class="form-group">
            <label for="class_id" class="form-label">
                Kelas Praktikum <span class="required">*</span>
            </label>

            <select
                id="class_id"
                name="class_id"
                class="form-control"
                required
                @disabled($classes->isEmpty())
            >
                <option value="">Pilih kelas praktikum</option>

                @foreach($classes as $class)
                    @php
                        $courseName = $class->course?->name ?? 'Mata kuliah tidak tersedia';
                        $courseCode = $class->course?->code;
                        $semesterName = $class->course?->studySemester?->name;
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

                        @if($semesterName)
                            - {{ $semesterName }}
                        @endif
                    </option>
                @endforeach
            </select>

            <div class="form-help">
                Pilih kelas praktikum yang akan menerima tugas ini.
            </div>

            @error('class_id')
                <div class="form-help" style="color: var(--danger);">
                    {{ $message }}
                </div>
            @enderror
        </div>
    @endif

    <div class="form-group">
        <label for="deadline" class="form-label">
            Deadline <span class="required">*</span>
        </label>

        <input
            id="deadline"
            type="datetime-local"
            name="deadline"
            class="form-control"
            value="{{ $deadlineValue }}"
            required
        >

        <div class="form-help">
            Batas akhir mahasiswa mengumpulkan tugas.
        </div>

        @error('deadline')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="title" class="form-label">
            Judul Tugas <span class="required">*</span>
        </label>

        <input
            id="title"
            type="text"
            name="title"
            class="form-control"
            value="{{ old('title', $assignment->title ?? null) }}"
            placeholder="Contoh: Tugas 1 - Instalasi Laravel"
            required
        >

        <div class="form-help">
            Gunakan judul yang jelas agar mahasiswa mudah mengenali tugas.
        </div>

        @error('title')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group">
        <label for="max_score" class="form-label">
            Nilai Maksimal <span class="required">*</span>
        </label>

        <input
            id="max_score"
            type="number"
            name="max_score"
            class="form-control"
            value="{{ old('max_score', $assignment->max_score ?? 100) }}"
            min="1"
            required
        >

        <div class="form-help">
            Nilai maksimal yang bisa diperoleh mahasiswa untuk tugas ini.
        </div>

        @error('max_score')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group" style="grid-column: 1 / -1;">
        <label for="description" class="form-label">
            Deskripsi
        </label>

        <textarea
            id="description"
            name="description"
            class="form-control"
            placeholder="Tuliskan instruksi, ketentuan, atau penjelasan tugas untuk mahasiswa."
        >{{ old('description', $assignment->description ?? null) }}</textarea>

        <div class="form-help">
            Deskripsi bersifat opsional, tetapi disarankan agar instruksi tugas lebih jelas.
        </div>

        @error('description')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group" style="grid-column: 1 / -1;">
        @if($hasExistingFile)
            <div class="alert" style="margin-bottom: 12px;">
                <strong>File instruksi sudah tersedia.</strong>
                Kosongkan input file jika tidak ingin mengganti file yang sudah tersimpan.
            </div>
        @endif

        <label for="file" class="form-label">
            File Instruksi Tugas
        </label>

        <input
            id="file"
            type="file"
            name="file"
            class="form-control"
            accept=".pdf,.docx,.txt,.md,.csv,application/pdf,text/plain,text/markdown,text/csv,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
        >

        <div class="form-help">
            Format yang disarankan: PDF, DOCX, TXT, MD, atau CSV.
            Kosongkan saat edit jika tidak ingin mengganti file.
        </div>

        @error('file')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>
</div>