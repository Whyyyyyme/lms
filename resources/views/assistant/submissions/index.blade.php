@extends('layouts.app')
@section('title', 'Submission Mahasiswa')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Submission Mahasiswa', 'description' => 'Lihat upload tugas mahasiswa dan input nilai/feedback.'])
<div class="toolbar">
    <form method="GET" class="actions-inline">
        <select class="form-control" name="status" style="width:180px;">
            <option value="">Semua status</option>
            <option value="graded" @selected(request('status') === 'graded')>Sudah dinilai</option>
            <option value="ungraded" @selected(request('status') === 'ungraded')>Belum dinilai</option>
        </select>
        <button class="btn">Filter</button>
    </form>
</div>
<div class="table-card"><table><thead><tr><th>Mahasiswa</th><th>Tugas</th><th>Dikumpulkan</th><th>Nilai</th><th>Aksi</th></tr></thead><tbody>
@forelse($submissions as $submission)
<tr><td>{{ $submission->student?->name }}</td><td>{{ $submission->assignment?->title }}</td><td>{{ optional($submission->submitted_at)->format('d M Y H:i') }}</td><td>{{ $submission->score ?? 'Belum dinilai' }}</td><td><a class="btn btn-sm btn-primary" href="{{ route('assistant.submissions.show', $submission) }}">Nilai</a></td></tr>
@empty <tr><td colspan="5">Belum ada submission.</td></tr> @endforelse
</tbody></table></div><div style="margin-top:16px;">{{ $submissions->links() }}</div>
@endsection
