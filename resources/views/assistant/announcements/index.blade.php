@extends('layouts.app')
@section('title', 'Pengumuman')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Pengumuman', 'description' => 'Kelola pengumuman untuk mahasiswa kelas praktikum.'])
<div class="toolbar"><span></span><a href="{{ route('assistant.pengumuman.create') }}" class="btn btn-primary">+ Buat Pengumuman</a></div>
<div class="table-card"><table><thead><tr><th>Judul</th><th>Kelas</th><th>Dibuat</th><th>Aksi</th></tr></thead><tbody>
@forelse($announcements as $announcement)
<tr><td><strong>{{ $announcement->title }}</strong><br><small>{{ str($announcement->content)->limit(100) }}</small></td><td>{{ $announcement->kelas?->course?->name }} - {{ $announcement->kelas?->name }}</td><td>{{ optional($announcement->created_at)->format('d M Y H:i') }}</td><td class="actions-inline"><a class="btn btn-sm" href="{{ route('assistant.pengumuman.show', $announcement) }}">Detail</a><a class="btn btn-sm" href="{{ route('assistant.pengumuman.edit', $announcement) }}">Edit</a>@include('partials.delete-button', ['action' => route('assistant.pengumuman.destroy', $announcement)])</td></tr>
@empty <tr><td colspan="4">Belum ada pengumuman.</td></tr> @endforelse
</tbody></table></div><div style="margin-top:16px;">{{ $announcements->links() }}</div>
@endsection
