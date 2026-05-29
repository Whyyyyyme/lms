@extends('layouts.app')
@section('title', 'Tugas')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Tugas Praktikum', 'description' => 'Kelola tugas, deadline, dan lampiran instruksi.'])
<div class="toolbar"><span></span><a href="{{ route('assistant.tugas.create') }}" class="btn btn-primary">+ Buat Tugas</a></div>
<div class="table-card"><table><thead><tr><th>Judul</th><th>Kelas</th><th>Deadline</th><th>Nilai Maks</th><th>Submission</th><th>Aksi</th></tr></thead><tbody>
@forelse($assignments as $assignment)
<tr><td><strong>{{ $assignment->title }}</strong><br><small>{{ str($assignment->description)->limit(80) }}</small></td><td>{{ $assignment->kelas?->course?->name }} - {{ $assignment->kelas?->name }}</td><td>{{ optional($assignment->deadline)->format('d M Y H:i') }}</td><td>{{ $assignment->max_score }}</td><td>{{ $assignment->submissions_count ?? $assignment->submissions->count() }}</td><td class="actions-inline"><a class="btn btn-sm" href="{{ route('assistant.tugas.show', $assignment) }}">Detail</a><a class="btn btn-sm" href="{{ route('assistant.tugas.edit', $assignment) }}">Edit</a>@include('partials.delete-button', ['action' => route('assistant.tugas.destroy', $assignment)])</td></tr>
@empty <tr><td colspan="6">Belum ada tugas.</td></tr> @endforelse
</tbody></table></div><div style="margin-top:16px;">{{ $assignments->links() }}</div>
@endsection
