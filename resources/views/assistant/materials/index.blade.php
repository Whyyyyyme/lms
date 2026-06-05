@extends('layouts.app')

@section('title', 'Materi Praktikum')

@section('content')
<section class="dashboard-hero">
    <div class="eyebrow">Asisten Praktikum</div>

    <h1>Materi Praktikum</h1>

    <p>
        Kelola materi praktikum yang bisa diakses mahasiswa.
        Materi dapat berupa file pembelajaran, PDF, dokumen, atau link sesuai data yang sudah tersedia di sistem.
    </p>

    <div class="hero-actions">
        <a href="{{ route('assistant.materi.create') }}" class="btn btn-primary">
            + Upload Materi
        </a>

        @if(\Illuminate\Support\Facades\Route::has('assistant.dashboard'))
            <a href="{{ route('assistant.dashboard') }}" class="btn">
                ← Dashboard
            </a>
        @endif
    </div>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Materi</h2>
            <div class="section-subtitle">
                Materi ditampilkan berdasarkan kelas praktikum yang kamu kelola.
            </div>
        </div>

        <a href="{{ route('assistant.materi.create') }}" class="btn btn-primary btn-sm">
            + Upload Materi
        </a>
    </div>

    @if($materials->isEmpty())
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">📘</div>

            <h3 class="empty-state-title">
                Belum ada materi
            </h3>

            <p class="empty-state-text">
                Materi yang kamu upload akan tampil di halaman ini.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Judul Materi</th>
                            <th>Kelas</th>
                            <th>Tipe</th>
                            <th>Publikasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($materials as $material)
                            <tr>
                                <td>
                                    <div style="display: grid; gap: 6px;">
                                        <strong>
                                            {{ $material->title }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ str($material->description)->limit(90) }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <div style="display: grid; gap: 6px;">
                                        <strong>
                                            {{ $material->kelas?->course?->name ?? 'Mata kuliah tidak ditemukan' }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            {{ $material->kelas?->name ?? 'Kelas tidak ditemukan' }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="status-pill status-info">
                                        {{ strtoupper($material->type ?? 'Materi') }}
                                    </span>
                                </td>

                                <td>
                                    <span class="item-meta" style="margin-top: 0;">
                                        {{ optional($material->published_at)->format('d M Y H:i') ?? '-' }}
                                    </span>
                                </td>

                                <td>
                                    <div class="actions-inline">
                                        <a
                                            class="btn btn-sm"
                                            href="{{ route('assistant.materi.show', $material) }}"
                                        >
                                            Detail
                                        </a>

                                        <a
                                            class="btn btn-sm"
                                            href="{{ route('assistant.materi.edit', $material) }}"
                                        >
                                            Edit
                                        </a>

                                        @include('partials.delete-button', [
                                            'action' => route('assistant.materi.destroy', $material)
                                        ])
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 18px;">
            {{ $materials->links() }}
        </div>
    @endif
</section>
@endsection