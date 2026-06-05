@extends('layouts.app')

@section('title', 'Tahun Akademik')

@section('content')
@php
    use Illuminate\Support\Facades\Route;

    $academicYears = $academicYears ?? collect();

    $dashboardUrl = Route::has('admin.dashboard')
        ? route('admin.dashboard')
        : '#';
@endphp

<section class="dashboard-hero">
    <div class="eyebrow">Admin</div>

    <h1>Tahun Akademik</h1>

    <p>
        Kelola periode tahun akademik untuk mata kuliah praktikum.
        Tahun akademik digunakan sebagai penanda periode ganjil atau genap pada data mata kuliah.
    </p>

    <div class="hero-actions">
        <a href="{{ $dashboardUrl }}" class="btn">
            ← Dashboard
        </a>

        <a href="{{ route('admin.tahun-akademik.create') }}" class="btn btn-primary">
            + Tambah Tahun Akademik
        </a>
    </div>
</section>

<section class="card" style="margin-bottom: 18px;">
    <div class="section-header">
        <div>
            <h2 class="section-title">Filter Tahun Akademik</h2>
            <div class="section-subtitle">
                Cari tahun akademik berdasarkan tahun, periode semester, atau status aktif.
            </div>
        </div>
    </div>

    <form method="GET" class="actions-inline">
        <input
            class="form-control"
            style="width: 230px;"
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari tahun akademik"
        >

        <select class="form-control" style="width: 170px;" name="semester">
            <option value="">Semua periode</option>

            <option value="ganjil" @selected(request('semester') === 'ganjil')>
                Ganjil
            </option>

            <option value="genap" @selected(request('semester') === 'genap')>
                Genap
            </option>
        </select>

        <select class="form-control" style="width: 160px;" name="status">
            <option value="">Semua status</option>

            <option value="1" @selected(request('status') === '1')>
                Aktif
            </option>

            <option value="0" @selected(request('status') === '0')>
                Nonaktif
            </option>
        </select>

        <button class="btn" type="submit">
            Filter
        </button>

        @if(request()->hasAny(['search', 'semester', 'status']))
            <a href="{{ route('admin.tahun-akademik.index') }}" class="btn">
                Reset
            </a>
        @endif
    </form>
</section>

<section class="card">
    <div class="section-header">
        <div>
            <h2 class="section-title">Daftar Tahun Akademik</h2>
            <div class="section-subtitle">
                Kelola tahun akademik, periode semester, status, dan jumlah mata kuliah yang terhubung.
            </div>
        </div>

        <a href="{{ route('admin.tahun-akademik.create') }}" class="btn btn-primary btn-sm">
            + Tambah Tahun Akademik
        </a>
    </div>

    @if($academicYears->count() === 0)
        <div class="empty-state">
            <div style="font-size: 34px; margin-bottom: 8px;">📅</div>

            <h3 class="empty-state-title">
                Belum ada tahun akademik
            </h3>

            <p class="empty-state-text">
                Data tahun akademik akan tampil setelah admin menambahkan periode tahun akademik.
            </p>
        </div>
    @else
        <div class="table-card">
            <div class="table-scroll">
                <table>
                    <thead>
                        <tr>
                            <th>Tahun</th>
                            <th>Periode</th>
                            <th>Status</th>
                            <th>Mata Kuliah</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach($academicYears as $academicYear)
                            @php
                                $coursesCount = (int) ($academicYear->courses_count ?? 0);
                            @endphp

                            <tr>
                                <td>
                                    <div style="display: grid; gap: 5px;">
                                        <strong>
                                            {{ $academicYear->year }}
                                        </strong>

                                        <span class="item-meta" style="margin-top: 0;">
                                            Tahun akademik {{ $academicYear->year }}
                                        </span>
                                    </div>
                                </td>

                                <td>
                                    <span class="status-pill {{ $academicYear->semester === 'ganjil' ? 'status-warning' : 'status-info' }}">
                                        {{ ucfirst($academicYear->semester) }}
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill {{ $academicYear->is_active ? 'status-success' : 'status-danger' }}">
                                        {{ $academicYear->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>

                                <td>
                                    <span class="status-pill status-muted">
                                        {{ $coursesCount }} Mata Kuliah
                                    </span>
                                </td>

                                <td>
                                    <div class="actions-inline">
                                        <a class="btn btn-sm" href="{{ route('admin.tahun-akademik.show', $academicYear) }}">
                                            Detail
                                        </a>

                                        <a class="btn btn-sm" href="{{ route('admin.tahun-akademik.edit', $academicYear) }}">
                                            Edit
                                        </a>

                                        @if($coursesCount === 0)
                                            @include('partials.delete-button', [
                                                'action' => route('admin.tahun-akademik.destroy', $academicYear),
                                                'confirm' => 'Yakin ingin menghapus tahun akademik ini?'
                                            ])
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div style="margin-top: 18px;">
            {{ $academicYears->links() }}
        </div>
    @endif
</section>
@endsection