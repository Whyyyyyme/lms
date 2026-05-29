@extends('layouts.app')
@section('title', 'Tahun Akademik')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Tahun Akademik', 'description' => 'Kelola semester ganjil/genap dan tahun aktif.'])
<div class="toolbar"><span></span><a href="{{ route('admin.tahun-akademik.create') }}" class="btn btn-primary">+ Tambah Tahun Akademik</a></div>
<div class="table-card"><table><thead><tr><th>Tahun</th><th>Semester</th><th>Status</th><th>Matakuliah</th><th>Aksi</th></tr></thead><tbody>
@forelse($academicYears as $academicYear)
<tr><td><strong>{{ $academicYear->year }}</strong></td><td>{{ ucfirst($academicYear->semester) }}</td><td><span class="badge {{ $academicYear->is_active ? 'badge-green' : '' }}">{{ $academicYear->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td>{{ $academicYear->courses_count ?? 0 }}</td><td class="actions-inline"><a class="btn btn-sm" href="{{ route('admin.tahun-akademik.show', $academicYear) }}">Detail</a><a class="btn btn-sm" href="{{ route('admin.tahun-akademik.edit', $academicYear) }}">Edit</a>@include('partials.delete-button', ['action' => route('admin.tahun-akademik.destroy', $academicYear)])</td></tr>
@empty <tr><td colspan="5">Belum ada tahun akademik.</td></tr> @endforelse
</tbody></table></div><div style="margin-top:16px;">{{ $academicYears->links() }}</div>
@endsection
