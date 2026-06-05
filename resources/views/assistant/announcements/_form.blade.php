@php
    $classes = $classes ?? collect();
@endphp

<div class="form-grid">
    <div class="form-group" style="grid-column: 1 / -1;">
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
                    @selected((string) old('class_id', $announcement->class_id ?? '') === (string) $class->id)
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
            Pilih kelas praktikum yang akan menerima pengumuman ini.
        </div>

        @error('class_id')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group" style="grid-column: 1 / -1;">
        <label for="title" class="form-label">
            Judul Pengumuman <span class="required">*</span>
        </label>

        <input
            id="title"
            type="text"
            name="title"
            class="form-control"
            value="{{ old('title', $announcement->title ?? null) }}"
            placeholder="Contoh: Informasi Praktikum Minggu Ini"
            required
        >

        <div class="form-help">
            Gunakan judul yang singkat dan jelas agar mudah dipahami mahasiswa.
        </div>

        @error('title')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>

    <div class="form-group" style="grid-column: 1 / -1;">
        <label for="content" class="form-label">
            Isi Pengumuman <span class="required">*</span>
        </label>

        <textarea
            id="content"
            name="content"
            class="form-control"
            placeholder="Tuliskan isi pengumuman untuk mahasiswa."
            required
        >{{ old('content', $announcement->content ?? null) }}</textarea>

        <div class="form-help">
            Isi pengumuman dapat berupa informasi materi, tugas, absensi, perubahan jadwal, atau instruksi praktikum.
        </div>

        @error('content')
            <div class="form-help" style="color: var(--danger);">
                {{ $message }}
            </div>
        @enderror
    </div>
</div>