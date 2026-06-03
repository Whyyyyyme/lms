@extends('layouts.app')

@section('title', 'Edit Tugas')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Asisten',
    'title' => 'Edit Tugas',
    'description' => 'Perbarui data tugas, deadline, file, dan waktu publikasi tugas.'
])

<form action="{{ route('assistant.tugas.update', $assignment) }}" method="POST" enctype="multipart/form-data" class="form-card">
    @csrf
    @method('PUT')

    @include('assistant.assignments._form')

    <div class="form-group">
        <label for="published_at" class="form-label">
            Waktu Publikasi
        </label>

        <input
            type="datetime-local"
            id="published_at"
            name="published_at"
            value="{{ old('published_at', $assignment->published_at ? $assignment->published_at->format('Y-m-d\TH:i') : '') }}"
            class="form-control @error('published_at') is-invalid @enderror"
        >

        <p class="form-help">
            Kosongkan jika tugas ingin langsung ditampilkan ke mahasiswa. Jika diisi dengan waktu masa depan, tugas baru akan muncul di halaman mahasiswa sesuai waktu publikasi ini.
        </p>

        @if($assignment->published_at)
            @if($assignment->published_at->isFuture())
                <p class="mt-2 text-sm text-yellow-700">
                    Status saat ini: tugas masih terjadwal dan akan tampil pada
                    <strong>{{ $assignment->published_at->format('d/m/Y H:i') }}</strong>.
                </p>
            @else
                <p class="mt-2 text-sm text-green-700">
                    Status saat ini: tugas sudah dipublikasikan sejak
                    <strong>{{ $assignment->published_at->format('d/m/Y H:i') }}</strong>.
                </p>
            @endif
        @else
            <p class="mt-2 text-sm text-green-700">
                Status saat ini: tugas langsung tampil ke mahasiswa.
            </p>
        @endif

        @error('published_at')
            <div class="form-error">
                {{ $message }}
            </div>
        @enderror
    </div>

    @include('partials.form.actions', [
        'cancel' => route('assistant.tugas.index'),
        'label' => 'Update Tugas'
    ])
</form>
@endsection