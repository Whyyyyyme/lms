<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Submission;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ExportController extends Controller
{
    use ResolvesClassAccess;

    public function scoresExcel()
    {
        $classIds = $this->assistantClassesQuery()->pluck('id');
        $rows = Submission::with(['student', 'assignment.kelas.course'])
            ->whereHas('assignment', fn ($query) => $query->whereIn('class_id', $classIds))
            ->latest('submitted_at')
            ->get();

        return Response::streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Mahasiswa', 'NIM', 'Matakuliah', 'Kelas', 'Tugas', 'Nilai', 'Feedback', 'Dikumpulkan']);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->student?->name,
                    $row->student?->nim_nip,
                    $row->assignment?->kelas?->course?->name,
                    $row->assignment?->kelas?->name,
                    $row->assignment?->title,
                    $row->score,
                    $row->feedback,
                    optional($row->submitted_at)->format('d/m/Y H:i'),
                ]);
            }

            fclose($handle);
        }, 'rekap-nilai.csv', ['Content-Type' => 'text/csv']);
    }

    public function scoresPdf(): View
    {
        $classIds = $this->assistantClassesQuery()->pluck('id');
        $submissions = Submission::with(['student', 'assignment.kelas.course'])
            ->whereHas('assignment', fn ($query) => $query->whereIn('class_id', $classIds))
            ->latest('submitted_at')
            ->get();

        return view('assistant.exports.scores-pdf', compact('submissions'));
    }

    public function attendancesExcel()
    {
        $classIds = $this->assistantClassesQuery()->pluck('id');
        $rows = Attendance::with(['kelas.course', 'records.student'])
            ->whereIn('class_id', $classIds)
            ->latest('session_date')
            ->get();

        return Response::streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Matakuliah', 'Kelas', 'Mahasiswa', 'NIM', 'Status', 'Waktu Check-in']);

            foreach ($rows as $attendance) {
                foreach ($attendance->records as $record) {
                    fputcsv($handle, [
                        optional($attendance->session_date)->format('d/m/Y'),
                        $attendance->kelas?->course?->name,
                        $attendance->kelas?->name,
                        $record->student?->name,
                        $record->student?->nim_nip,
                        $record->status,
                        optional($record->checked_at)->format('d/m/Y H:i'),
                    ]);
                }
            }

            fclose($handle);
        }, 'rekap-absensi.csv', ['Content-Type' => 'text/csv']);
    }

    public function attendancesPdf(): View
    {
        $classIds = $this->assistantClassesQuery()->pluck('id');
        $attendances = Attendance::with(['kelas.course', 'records.student'])
            ->whereIn('class_id', $classIds)
            ->latest('session_date')
            ->get();

        return view('assistant.exports.attendances-pdf', compact('attendances'));
    }
}
