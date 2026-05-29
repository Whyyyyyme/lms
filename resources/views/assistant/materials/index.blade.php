@extends('layouts.app')
@section('title', 'Materi')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Materi Praktikum', 'description' => 'Kelola materi yang bisa diakses mahasiswa.'])
<div class="toolbar"><span></span><a href="{{ route('assistant.materi.create') }}" class="btn btn-primary">+ Upload Materi</a></div>
<div class="table-card"><table><thead><tr><th>Judul</th><th>Kelas</th><th>Tipe</th><th>Publikasi</th><th>Aksi</th></tr></thead><tbody>
@forelse($materials as $material)
<tr><td><strong>{{ $material->title }}</strong><br><small>{{ str($material->description)->limit(80) }}</small></td><td>{{ $material->kelas?->course?->name }} - {{ $material->kelas?->name }}</td><td>{{ strtoupper($material->type) }}</td><td>{{ optional($material->published_at)->format('d M Y H:i') ?? '-' }}</td><td class="actions-inline"><a class="btn btn-sm" href="{{ route('assistant.materi.show', $material) }}">Detail</a><a class="btn btn-sm" href="{{ route('assistant.materi.edit', $material) }}">Edit</a>@include('partials.delete-button', ['action' => route('assistant.materi.destroy', $material)])</td></tr>
@empty <tr><td colspan="5">Belum ada materi.</td></tr> @endforelse
</tbody></table></div><div style="margin-top:16px;">{{ $materials->links() }}</div>
@endsection
