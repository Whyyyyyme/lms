<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\LmsNotification;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function scores(Request $request): View
    {
        $submissions = Submission::query()
            ->with(['student', 'assignment.kelas.course'])
            ->when($request->filled('course_id'), function ($query) use ($request) {
                $query->whereHas('assignment.kelas', fn ($query) => $query->where('course_id', $request->integer('course_id')));
            })
            ->latest('submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.scores', [
            'submissions' => $submissions,
            'courses' => Course::orderBy('name')->get(),
        ]);
    }

    public function attendances(Request $request): View
    {
        $attendances = Attendance::query()
            ->with(['kelas.course', 'opener'])
            ->withCount('records')
            ->when($request->filled('course_id'), fn ($query) => $query->whereHas('kelas', fn ($query) => $query->where('course_id', $request->integer('course_id'))))
            ->latest('session_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.attendances', [
            'attendances' => $attendances,
            'courses' => Course::orderBy('name')->get(),
        ]);
    }

    public function activities(): View
    {
        $activities = LmsNotification::query()
            ->with('user')
            ->latest()
            ->paginate(20);

        return view('admin.reports.activities', compact('activities'));
    }

    public function exportScores(Request $request)
    {
        $rows = Submission::query()
            ->with(['student', 'assignment.kelas.course'])
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
        }, 'laporan-nilai.csv', ['Content-Type' => 'text/csv']);
    }

    public function exportAttendances(Request $request)
    {
        $rows = Attendance::query()
            ->with(['kelas.course', 'records.student'])
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
        }, 'laporan-absensi.csv', ['Content-Type' => 'text/csv']);
    }
}
