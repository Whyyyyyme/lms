@extends('layouts.app', ['title' => $material->title])

@section('content')
    @include('partials.page-header', [
        'eyebrow' => 'Mahasiswa',
        'title' => $material->title,
        'description' => $material->kelas?->course?->name . ' - ' . $material->kelas?->name,
    ])

    <div class="mb-5 flex flex-wrap gap-2">
        @if ($material->kelas?->course)
            <a href="{{ route('student.materials.course', $material->kelas->course) }}"
                class="inline-flex rounded-2xl border bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                ← Kembali ke Materi {{ $material->kelas->course->name }}
            </a>
        @else
            <a href="{{ route('student.materials.index') }}"
                class="inline-flex rounded-2xl border bg-white px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                ← Kembali ke Materi
            </a>
        @endif

        @if (!empty($viewer['download_url']) && $viewer['type'] !== 'youtube')
            <a href="{{ $viewer['download_url'] }}"
                class="inline-flex rounded-2xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                Download Materi
            </a>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_500px]">
        <section class="rounded-3xl border bg-white p-5 shadow-sm">
            @if ($viewer['type'] === 'pdf')
                <div class="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="font-black text-slate-950">
                            PDF Reader
                        </h2>

                        <p class="text-sm text-slate-500">
                            Materi PDF dapat dibaca langsung di halaman ini.
                        </p>
                    </div>

                    @if (!empty($viewer['download_url']))
                        <a href="{{ $viewer['download_url'] }}"
                            class="rounded-2xl border bg-white px-4 py-2 text-sm font-bold text-slate-700 hover:bg-slate-50">
                            Download
                        </a>
                    @endif
                </div>

                <div class="overflow-hidden rounded-3xl border bg-slate-100">
                    <iframe src="{{ $viewer['embed_url'] }}#toolbar=1&navpanes=0&scrollbar=1" class="h-[75vh] w-full"
                        title="PDF Reader {{ $material->title }}">
                    </iframe>
                </div>
            @elseif($viewer['type'] === 'video_iframe')
                <div class="mb-4">
                    <h2 class="font-black text-slate-950">
                        Video Pembelajaran
                    </h2>

                    <p class="text-sm text-slate-500">
                        Video dapat diputar langsung di halaman ini.
                    </p>
                </div>

                <div class="overflow-hidden rounded-3xl border bg-black">
                    <iframe src="{{ $viewer['embed_url'] }}" class="aspect-video w-full"
                        title="Video {{ $material->title }}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                        allowfullscreen>
                    </iframe>
                </div>
            @elseif($viewer['type'] === 'video_file')
                <div class="mb-4">
                    <h2 class="font-black text-slate-950">
                        Video Pembelajaran
                    </h2>

                    <p class="text-sm text-slate-500">
                        File video dapat diputar langsung di halaman ini.
                    </p>
                </div>

                <div class="overflow-hidden rounded-3xl border bg-black">
                    <video controls class="aspect-video w-full bg-black">
                        <source src="{{ $viewer['embed_url'] }}">
                        Browser Anda tidak mendukung pemutar video.
                    </video>
                </div>
            @elseif($viewer['type'] === 'file')
                <div class="rounded-3xl border border-amber-200 bg-amber-50 p-6">
                    <h2 class="font-black text-amber-900">
                        Preview tidak tersedia
                    </h2>

                    <p class="mt-2 text-sm text-amber-800">
                        {{ $viewer['message'] }}
                    </p>

                    @if (!empty($viewer['download_url']))
                        <a href="{{ $viewer['download_url'] }}"
                            class="mt-4 inline-flex rounded-2xl bg-amber-600 px-4 py-2 text-sm font-bold text-white hover:bg-amber-700">
                            Download File
                        </a>
                    @endif
                </div>
            @elseif($viewer['type'] === 'link')
                <div class="rounded-3xl border border-blue-200 bg-blue-50 p-6">
                    <h2 class="font-black text-blue-900">
                        Link Materi
                    </h2>

                    <p class="mt-2 text-sm text-blue-800">
                        Materi ini berupa link eksternal. Jika link tersebut bukan YouTube, Google Drive, Vimeo, Loom, PDF,
                        atau file video langsung, sistem tidak bisa menampilkannya langsung.
                    </p>

                    <a href="{{ $viewer['url'] }}" target="_blank" rel="noopener"
                        class="mt-4 inline-flex rounded-2xl bg-blue-600 px-4 py-2 text-sm font-bold text-white hover:bg-blue-700">
                        Buka Link
                    </a>
                </div>
            @else
                <div class="rounded-3xl border bg-slate-50 p-6">
                    <h2 class="font-black text-slate-900">
                        Materi belum tersedia
                    </h2>

                    <p class="mt-2 text-sm text-slate-500">
                        {{ $viewer['message'] }}
                    </p>
                </div>
            @endif
        </section>

        <aside class="space-y-4">
            <section class="rounded-3xl border bg-white p-5 shadow-sm">
                <h2 class="font-black text-slate-950">
                    Informasi Materi
                </h2>

                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">
                            Mata Kuliah
                        </p>
                        <p class="font-semibold text-slate-800">
                            {{ $material->kelas?->course?->name ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">
                            Kelas
                        </p>
                        <p class="font-semibold text-slate-800">
                            {{ $material->kelas?->name ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">
                            Tipe Materi
                        </p>
                        <p class="font-semibold text-slate-800">
                            {{ strtoupper($material->type ?? $viewer['type']) }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">
                            Dipublikasikan
                        </p>
                        <p class="font-semibold text-slate-800">
                            {{ $material->published_at?->format('d M Y H:i') ?? '-' }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">
                            Dibuat oleh
                        </p>
                        <p class="font-semibold text-slate-800">
                            {{ $material->creator?->name ?? '-' }}
                        </p>
                    </div>
                </div>
            </section>

            <section class="rounded-3xl border bg-white p-5 shadow-sm">
                <h2 class="font-black text-slate-950">
                    Deskripsi
                </h2>

                <div class="prose prose-sm mt-3 max-w-none text-slate-600">
                    {!! nl2br(e($material->description ?: 'Tidak ada deskripsi.')) !!}
                </div>
            </section>
        </aside>
    </div>
@endsection
