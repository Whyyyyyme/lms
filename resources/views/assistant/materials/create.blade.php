@extends('layouts.app', ['title' => 'Tambah Materi'])

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Asisten Praktikum',
    'title' => 'Tambah Materi',
    'description' => 'Unggah materi PDF atau tambahkan link video pembelajaran.',
])

<form action="{{ route('assistant.materi.store') }}"
      method="POST"
      enctype="multipart/form-data"
      x-data="{ type: '{{ old('type', 'pdf') }}' }"
      class="space-y-5 rounded-3xl border bg-white p-6 shadow-sm">
    @csrf

    <div>
        <label class="mb-2 block text-sm font-bold text-slate-700">
            Kelas Praktikum
        </label>

        <select name="class_id"
                class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required>
            <option value="">Pilih kelas</option>

            @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected(old('class_id') == $class->id)>
                    {{ $class->course?->name }} - {{ $class->name }}
                </option>
            @endforeach
        </select>

        @error('class_id')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-bold text-slate-700">
            Judul Materi
        </label>

        <input type="text"
               name="title"
               value="{{ old('title') }}"
               class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
               placeholder="Contoh: Pertemuan 1 - Pengenalan Laravel"
               required>

        @error('title')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-bold text-slate-700">
            Deskripsi
        </label>

        <textarea name="description"
                  rows="4"
                  class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                  placeholder="Tulis deskripsi singkat materi...">{{ old('description') }}</textarea>

        @error('description')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-bold text-slate-700">
            Tipe Materi
        </label>

        <select name="type"
                x-model="type"
                class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                required>
            <option value="pdf">PDF</option>
            <option value="link">Link Video</option>
        </select>

        @error('type')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div x-show="type === 'pdf'" x-cloak>
        <label class="mb-2 block text-sm font-bold text-slate-700">
            Upload File PDF
        </label>

        <input type="file"
               name="file"
               accept="application/pdf,.pdf"
               class="w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm shadow-sm file:mr-4 file:rounded-xl file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-bold file:text-indigo-700 hover:file:bg-indigo-100">

        <p class="mt-2 text-xs text-slate-500">
            Hanya file PDF. Maksimal 100 MB.
        </p>

        @error('file')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div x-show="type === 'link'" x-cloak>
        <label class="mb-2 block text-sm font-bold text-slate-700">
            Link Video
        </label>

        <input type="url"
               name="link"
               value="{{ old('link') }}"
               class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
               placeholder="Contoh: https://www.youtube.com/watch?v=xxxx atau link Google Drive">

        <p class="mt-2 text-xs text-slate-500">
            Bisa menggunakan link YouTube, Google Drive, Vimeo, Loom, atau link video langsung.
        </p>

        @error('link')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label class="mb-2 block text-sm font-bold text-slate-700">
            Waktu Publikasi
        </label>

        <input type="datetime-local"
               name="published_at"
               value="{{ old('published_at') }}"
               class="w-full rounded-2xl border-slate-200 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">

        <p class="mt-2 text-xs text-slate-500">
            Kosongkan jika ingin langsung dipublikasikan.
        </p>

        @error('published_at')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="flex flex-wrap gap-2">
        <button type="submit"
                class="rounded-2xl bg-indigo-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-indigo-700">
            Simpan Materi
        </button>

        <a href="{{ route('assistant.materi.index') }}"
           class="rounded-2xl border bg-white px-5 py-2.5 text-sm font-bold text-slate-700 hover:bg-slate-50">
            Batal
        </a>
    </div>
</form>
@endsection
