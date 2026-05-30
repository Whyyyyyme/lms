@extends('layouts.app')

@section('title', 'Tahun Akademik')

@section('content')
@include('partials.page-header', [
    'eyebrow' => 'Admin',
    'title' => 'Tahun Akademik',
    'description' => 'Kelola periode tahun akademik untuk mata kuliah praktikum.'
])

<div class="toolbar">
    <form method="GET" class="actions-inline">
        <input
            class="form-control"
            style="width:220px;"
            type="search"
            name="search"
            value="{{ request('search') }}"
            placeholder="Cari tahun akademik"
        >

        <select class="form-control" style="width:160px;" name="semester">
            <option value="">Semua periode</option>
            <option value="ganjil" @selected(request('semester') === 'ganjil')>Ganjil</option>
            <option value="genap" @selected(request('semester') === 'genap')>Genap</option>
        </select>

        <select class="form-control" style="width:150px;" name="status">
            <option value="">Semua status</option>
            <option value="1" @selected(request('status') === '1')>Aktif</option>
            <option value="0" @selected(request('status') === '0')>Nonaktif</option>
        </select>

        <button class="btn" type="submit">Filter</button>

        @if(request()->hasAny(['search', 'semester', 'status']))
            <a href="{{ route('admin.tahun-akademik.index') }}" class="btn">Reset</a>
        @endif
    </form>

    <a href="{{ route('admin.tahun-akademik.create') }}" class="btn btn-primary">
        + Tambah Tahun Akademik
    </a>
</div>

<div class="table-card">
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
            @forelse($academicYears as $academicYear)
                <tr>
                    <td>
                        <strong>{{ $academicYear->year }}</strong>
                    </td>

                    <td>
                        {{ ucfirst($academicYear->semester) }}
                    </td>

                    <td>
                        <span class="badge {{ $academicYear->is_active ? 'badge-green' : 'badge-red' }}">
                            {{ $academicYear->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>

                    <td>
                        {{ $academicYear->courses_count ?? 0 }}
                    </td>

                    <td class="actions-inline">
                        <a class="btn btn-sm" href="{{ route('admin.tahun-akademik.show', $academicYear) }}">
                            Detail
                        </a>

                        <a class="btn btn-sm" href="{{ route('admin.tahun-akademik.edit', $academicYear) }}">
                            Edit
                        </a>

                        @if(($academicYear->courses_count ?? 0) === 0)
                            @include('partials.delete-button', [
                                'action' => route('admin.tahun-akademik.destroy', $academicYear),
                                'confirm' => 'Yakin ingin menghapus tahun akademik ini?'
                            ])
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        Belum ada tahun akademik.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div style="margin-top:16px;">
    {{ $academicYears->links() }}
</div>
@endsection