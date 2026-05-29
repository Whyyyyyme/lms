@extends('layouts.app')
@section('title', 'Matakuliah')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Matakuliah', 'description' => 'Kelola matakuliah aktif/nonaktif per tahun akademik.'])
<div class="toolbar"><form method="GET" class="actions-inline"><input class="form-control" style="width:260px" name="search" value="{{ request('search') }}" placeholder="Cari matakuliah/kode"><button class="btn">Cari</button></form><a href="{{ route('admin.matakuliah.create') }}" class="btn btn-primary">+ Tambah Matakuliah</a></div>
<div class="table-card"><table><thead><tr><th>Kode</th><th>Nama</th><th>Tahun Akademik</th><th>SKS</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
@forelse($courses as $course)
<tr><td>{{ $course->code }}</td><td><strong>{{ $course->name }}</strong></td><td>{{ $course->academicYear?->year }} - {{ ucfirst($course->academicYear?->semester ?? '') }}</td><td>{{ $course->sks }}</td><td><span class="badge {{ $course->is_active ? 'badge-green' : '' }}">{{ $course->is_active ? 'Aktif' : 'Nonaktif' }}</span></td><td class="actions-inline"><a class="btn btn-sm" href="{{ route('admin.matakuliah.show', $course) }}">Detail</a><a class="btn btn-sm" href="{{ route('admin.matakuliah.edit', $course) }}">Edit</a>@include('partials.delete-button', ['action' => route('admin.matakuliah.destroy', $course)])</td></tr>
@empty <tr><td colspan="6">Belum ada matakuliah.</td></tr> @endforelse
</tbody></table></div><div style="margin-top:16px;">{{ $courses->links() }}</div>
@endsection
