@php
    $selectedClass = $selectedClass ?? null;
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
        </select></label>
    @endif

    @include('partials.form.select', [
        'label' => 'Tipe Materi',
        'name' => 'type',
        'required' => true
    ])
        <option value="pdf" @selected($currentType === 'pdf')>
            PDF
        </option>

        <option value="link" @selected($currentType === 'link')>
            Link
        </option>
    </select></label>

    <div style="grid-column:1/-1;">
        @include('partials.form.input', [
            'label' => 'Judul Materi',
            'name' => 'title',
            'value' => old('title', $material->title ?? null),
            'required' => true,
            'placeholder' => 'Contoh: Pertemuan 1 - Pengenalan Laravel'
        ])
    </div>

    <div style="grid-column:1/-1;">
        @include('partials.form.textarea', [
            'label' => 'Deskripsi',
            'name' => 'description',
            'value' => old('description', $material->description ?? null)
        ])
    </div>

    <div style="grid-column:1/-1;" x-show="type === 'pdf'" x-cloak>
        @if($hasExistingPdf)
            <div class="alert" style="margin-bottom:12px;">
                File PDF saat ini sudah tersimpan. Kosongkan input upload PDF jika tidak ingin mengganti file.
            </div>
        @endif

        <label class="form-label" for="file">
            Upload File PDF
        </label>

        <input
            id="file"
            class="form-control"
            type="file"
            name="file"
            accept="application/pdf,.pdf"
            :required="type === 'pdf' && ! hasExistingPdf"
        >

        <small>
            Hanya file PDF. Maksimal 100 MB.
        </small>

        @error('file')
            <div class="text-danger" style="margin-top:6px;">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div style="grid-column:1/-1;" x-show="type === 'link'" x-cloak>
        @include('partials.form.input', [
            'label' => 'Link Materi',
            'name' => 'link',
            'type' => 'url',
            'value' => $linkValue,
            'placeholder' => 'https://youtube.com/... atau https://drive.google.com/...',
            'help' => 'Bisa menggunakan link YouTube, Google Drive, Vimeo, Loom, website, atau sumber materi online lainnya.'
        ])
    </div>

    <div style="grid-column:1/-1;">
        @include('partials.form.input', [
            'label' => 'Waktu Publikasi',
            'name' => 'published_at',
            'type' => 'datetime-local',
            'value' => $publishedAtValue,
            'help' => 'Kosongkan jika ingin langsung dipublikasikan.'
        ])
    </div>
</div>
