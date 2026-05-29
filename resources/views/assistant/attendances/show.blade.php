@extends('layouts.app')
@section('title', 'Kelola Absensi')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Kelola Absensi'])
<div class="form-card">
    <p><strong>Kelas:</strong> {{ $attendance->kelas?->course?->name }} - {{ $attendance->kelas?->name }}</p>
    <p><strong>Tanggal:</strong> {{ optional($attendance->session_date)->format('d M Y') ?? $attendance->session_date }}</p>
    <p><strong>Status:</strong> {{ $attendance->is_open ? 'Dibuka' : 'Ditutup' }}</p>
    <div class="actions-inline" style="margin:12px 0;">
        <form action="{{ route('assistant.attendances.open', $attendance) }}" method="POST">@csrf @method('PATCH')<button class="btn btn-primary">Buka</button></form>
        <form action="{{ route('assistant.attendances.close', $attendance) }}" method="POST">@csrf @method('PATCH')<button class="btn">Tutup</button></form>
    </div>
</div>
<div style="height:16px;"></div>
<div class="table-card"><table><thead><tr><th>Mahasiswa</th><th>Status</th><th>Check-in</th><th>Aksi</th></tr></thead><tbody>
@forelse($attendance->records as $record)
<tr><td>{{ $record->student?->name }}</td><td>{{ ucfirst($record->status) }}</td><td>{{ optional($record->checked_at)->format('d M Y H:i') ?? '-' }}</td><td><form action="{{ route('assistant.attendances.records.update', ['attendance' => $attendance, 'record' => $record]) }}" method="POST" class="actions-inline">@csrf @method('PATCH')<select class="form-control" name="status" style="width:130px;"><option value="hadir" @selected($record->status === 'hadir')>Hadir</option><option value="izin" @selected($record->status === 'izin')>Izin</option><option value="alpha" @selected($record->status === 'alpha')>Alpha</option></select><button class="btn btn-sm">Simpan</button></form></td></tr>
@empty <tr><td colspan="4">Belum ada record absensi.</td></tr> @endforelse
</tbody></table></div>
@endsection
