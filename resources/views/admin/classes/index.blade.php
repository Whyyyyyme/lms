@extends('layouts.app')
@section('title', 'Kelas Praktikum')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Admin', 'title' => 'Kelas Praktikum', 'description' => 'Kelola kelas, jadwal, ruangan, asisten, dan mahasiswa.'])
<div class="toolbar"><span></span><a href="{{ route('admin.kelas.create') }}" class="btn btn-primary">+ Tambah Kelas</a></div>
<div class="table-card"><table><thead><tr><th>Kelas</th><th>Matakuliah</th><th>Asisten</th><th>Jadwal</th><th>Mahasiswa</th><th>Aksi</th></tr></thead><tbody>
@forelse($classes as $class)
<tr><td><strong>{{ $class->name }}</strong><br><small>{{ $class->room ?? '-' }}</small></td><td>{{ $class->course?->name }}</td><td>{{ $class->assistant?->name ?? '-' }}</td><td>{{ $class->schedule ?? '-' }}</td><td>{{ $class->students_count ?? 0 }}</td><td class="actions-inline"><a class="btn btn-sm" href="{{ route('admin.kelas.show', $class) }}">Detail</a><a class="btn btn-sm" href="{{ route('admin.kelas.edit', $class) }}">Edit</a>@include('partials.delete-button', ['action' => route('admin.kelas.destroy', $class)])</td></tr>
@empty <tr><td colspan="6">Belum ada kelas praktikum.</td></tr> @endforelse
</tbody></table></div><div style="margin-top:16px;">{{ $classes->links() }}</div>
@endsection
