@extends('layouts.app')
@section('title', 'Absensi')
@section('content')
@include('partials.page-header', ['eyebrow' => 'Asisten', 'title' => 'Absensi Praktikum', 'description' => 'Buka/tutup sesi absensi dan rekap kehadiran mahasiswa.'])
<div class="toolbar"><span></span><a href="{{ route('assistant.attendances.create') }}" class="btn btn-primary">+ Buat Sesi Absensi</a></div>
<div class="table-card"><table><thead><tr><th>Kelas</th><th>Tanggal</th><th>Status</th><th>Records</th><th>Aksi</th></tr></thead><tbody>
@forelse($attendances as $attendance)
<tr><td>{{ $attendance->kelas?->course?->name }} - {{ $attendance->kelas?->name }}</td><td>{{ optional($attendance->session_date)->format('d M Y') ?? $attendance->session_date }}</td><td><span class="badge {{ $attendance->is_open ? 'badge-green' : '' }}">{{ $attendance->is_open ? 'Dibuka' : 'Ditutup' }}</span></td><td>{{ $attendance->records_count ?? $attendance->records->count() }}</td><td class="actions-inline"><a class="btn btn-sm" href="{{ route('assistant.attendances.show', $attendance) }}">Kelola</a>@include('partials.delete-button', ['action' => route('assistant.attendances.destroy', $attendance)])</td></tr>
@empty <tr><td colspan="5">Belum ada sesi absensi.</td></tr> @endforelse
</tbody></table></div><div style="margin-top:16px;">{{ $attendances->links() }}</div>
@endsection
