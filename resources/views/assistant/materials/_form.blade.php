@php
    $selectedClass = $selectedClass ?? null;
    $classes = $classes ?? collect();

    $currentType = old('type', $material->type ?? 'pdf');

    $currentFilePath = (string) ($material->file_path ?? '');
    $currentIsLink = $currentFilePath !== '' && str_starts_with($currentFilePath, 'http');

    $hasExistingPdf = isset($material)
        && ($material->type ?? null) === 'pdf'
        && $currentFilePath !== ''
        && ! $currentIsLink;

    $linkValue = old('link', $currentIsLink ? $currentFilePath : null);

    $publishedAtValue = old(
        'published_at',
        isset($material) && $material->published_at
            ? $material->published_at->timezone(config('app.timezone', 'Asia/Jakarta'))->format('Y-m-d\TH:i')
            : now(config('app.timezone', 'Asia/Jakarta'))->format('Y-m-d\TH:i')
    );
@endphp

<div
    class="form-grid"
    x-data="{
        type: @js($currentType),
        hasExistingPdf: @js($hasExistingPdf)
    }"
>
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
                        @selected((string) old('class_id', $material->class_id ?? '') === (string) $class->id)
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
                Pilih kelas praktikum yang akan menerima materi ini.
            </div>

            @error('class_id')
                <div class="form-help" style="color: var(--danger);">
                    {{ $message }}
                </div>
            @enderror
        </div>
    @endif

    <div class="form-group">
        <label for="type" class="form-label">
            Tipe Materi <span class="required">*</span>
        </label>

        <select
            id="type"
            name="type"
            class="form-control"
            x-model="type"
            required
        >
            <option value="pdf" @selected($currentType === 'pdf')>
                PDF
            </option>

            <option value="link" @selected($currentType === 'link')>
                Link
            </option>
        </select>

        <div class="form-help">
            Pilih PDF jika materi berupa file, atau Link jika materi berasal dari YouTube, Google Drive, dan sumber online lainnya.
        </div>

        @error('type')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group" style="grid-column: 1 / -1;">
        <label for="title" class="form-label">
            Judul Materi <span class="required">*</span>
        </label>

        <input
            id="title"
            type="text"
            name="title"
            class="form-control"
            value="{{ old('title', $material->title ?? null) }}"
            placeholder="Contoh: Pertemuan 1 - Pengenalan Laravel"
            required
        >

        <div class="form-help">
            Gunakan judul yang jelas agar mahasiswa mudah memahami isi materi.
        </div>

        @error('title')
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
            placeholder="Tuliskan ringkasan materi atau instruksi singkat untuk mahasiswa."
        >{{ old('description', $material->description ?? null) }}</textarea>

        <div class="form-help">
            Deskripsi bersifat opsional, tetapi disarankan agar mahasiswa mengetahui konteks materi.
        </div>

        @error('description')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group" style="grid-column: 1 / -1;" x-show="type === 'pdf'" x-cloak>
        @if($hasExistingPdf)
            <div class="alert" style="margin-bottom: 12px;">
                <strong>File PDF sudah tersedia.</strong>
                Kosongkan input upload PDF jika tidak ingin mengganti file yang sudah tersimpan.
            </div>
        @endif

        <label for="file" class="form-label">
            Upload File PDF
            <span class="required" x-show="type === 'pdf' && ! hasExistingPdf">*</span>
        </label>

        <input
            id="file"
            class="form-control"
            type="file"
            name="file"
            accept="application/pdf,.pdf"
            :required="type === 'pdf' && ! hasExistingPdf"
        >

        <div class="form-help">
            Hanya file PDF. Maksimal 100 MB.
        </div>

        @error('file')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group" style="grid-column: 1 / -1;" x-show="type === 'link'" x-cloak>
        <label for="link" class="form-label">
            Link Materi <span class="required" x-show="type === 'link'">*</span>
        </label>

        <input
            id="link"
            type="url"
            name="link"
            class="form-control"
            value="{{ $linkValue }}"
            placeholder="https://youtube.com/... atau https://drive.google.com/..."
            :required="type === 'link'"
        >

        <div class="form-help">
            Bisa menggunakan link YouTube, Google Drive, Vimeo, Loom, website, atau sumber materi online lainnya.
        </div>

        @error('link')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group" style="grid-column: 1 / -1;">
        <label for="published_at" class="form-label">
            Waktu Publikasi
        </label>

        <input
            id="published_at"
            type="datetime-local"
            name="published_at"
            class="form-control"
            value="{{ $publishedAtValue }}"
        >

        <div class="form-help">
            Atur waktu publikasi materi. Jika dikosongkan dan controller mengizinkan, materi dapat langsung dipublikasikan.
        </div>

        @error('published_at')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>
</div>