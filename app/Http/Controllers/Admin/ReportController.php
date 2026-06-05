<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\UsesCaseInsensitiveSearch;
use App\Models\Attendance;
use App\Models\AttendanceRecord;
use App\Models\Course;
use App\Models\LmsNotification;
use App\Models\PraktikumClass;
use App\Models\StudySemester;
use App\Models\Submission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\View\View;

class ReportController extends Controller
{
    use UsesCaseInsensitiveSearch;

    public function scores(Request $request): View
    {
        $query = $this->scoreReportQuery($request);

        $scoreStatsQuery = clone $query;

        $statistics = [
            'total_submissions' => (clone $scoreStatsQuery)->count(),
            'graded_submissions' => (clone $scoreStatsQuery)->whereNotNull('graded_at')->count(),
            'ungraded_submissions' => (clone $scoreStatsQuery)->whereNull('graded_at')->count(),
            'average_score' => round((float) (clone $scoreStatsQuery)->whereNotNull('score')->avg('score'), 2),
        ];

        $submissions = $query
            ->latest('submitted_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.scores', [
            'submissions' => $submissions,
            'statistics' => $statistics,
            'studySemesters' => StudySemester::orderBy('level')->get(),
            'courses' => Course::with('studySemester')->orderBy('name')->get(),
            'classes' => PraktikumClass::with(['course.studySemester'])->orderBy('name')->get(),
        ]);
    }

    public function attendances(Request $request): View
    {
        $query = $this->attendanceReportQuery($request);

        $attendanceIds = (clone $query)->pluck('id');

        $statistics = [
            'total_sessions' => $attendanceIds->count(),
            'open_sessions' => (clone $query)->where('is_open', true)->count(),
            'closed_sessions' => (clone $query)->where('is_open', false)->count(),
            'total_records' => AttendanceRecord::whereIn('attendance_id', $attendanceIds)->count(),
            'hadir_records' => AttendanceRecord::whereIn('attendance_id', $attendanceIds)->where('status', 'hadir')->count(),
            'izin_records' => AttendanceRecord::whereIn('attendance_id', $attendanceIds)->where('status', 'izin')->count(),
            'alpha_records' => AttendanceRecord::whereIn('attendance_id', $attendanceIds)->where('status', 'alpha')->count(),
        ];

        $attendances = $query
            ->withCount([
                'records',
                'records as hadir_count' => function ($query) {
                    $query->where('status', 'hadir');
                },
                'records as izin_count' => function ($query) {
                    $query->where('status', 'izin');
                },
                'records as alpha_count' => function ($query) {
                    $query->where('status', 'alpha');
                },
            ])
            ->latest('session_date')
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.attendances', [
            'attendances' => $attendances,
            'statistics' => $statistics,
            'studySemesters' => StudySemester::orderBy('level')->get(),
            'courses' => Course::with('studySemester')->orderBy('name')->get(),
            'classes' => PraktikumClass::with(['course.studySemester'])->orderBy('name')->get(),
        ]);
    }

    public function activities(Request $request): View
    {
        $query = $this->activityReportQuery($request);

        $statistics = [
            'total_activities' => (clone $query)->count(),
            'unread_activities' => (clone $query)->whereNull('read_at')->count(),
            'read_activities' => (clone $query)->whereNotNull('read_at')->count(),
            'today_activities' => (clone $query)->whereDate('created_at', today())->count(),
        ];

        $activities = $query
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports.activities', [
            'activities' => $activities,
            'statistics' => $statistics,
            'notificationTypes' => LmsNotification::query()
                ->lmsRows()
                ->whereNotNull('type')
                ->distinct()
                ->orderBy('type')
                ->pluck('type'),
        ]);
    }

    public function exportScores(Request $request)
    {
        $rows = $this->scoreReportQuery($request)
            ->latest('submitted_at')
            ->get();

        return Response::streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            // Supaya CSV aman dibuka di Excel.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Mahasiswa',
                'NIM',
                'Semester Mahasiswa',
                'Mata Kuliah',
                'Kode Mata Kuliah',
                'Kelas',
                'Tugas',
                'Nilai',
                'Nilai Maksimal',
                'Status Penilaian',
                'Feedback',
                'Dikumpulkan',
                'Dinilai Pada',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->student?->name,
                    $row->student?->nim_nip,
                    $row->student?->studySemester?->name,
                    $row->assignment?->kelas?->course?->name,
                    $row->assignment?->kelas?->course?->code,
                    $row->assignment?->kelas?->name,
                    $row->assignment?->title,
                    $row->score,
                    $row->assignment?->max_score,
                    $row->graded_at ? 'Sudah dinilai' : 'Belum dinilai',
                    $row->feedback,
                    optional($row->submitted_at)->format('d/m/Y H:i'),
                    optional($row->graded_at)->format('d/m/Y H:i'),
                ]);
            }

            fclose($handle);
        }, 'laporan-nilai.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportAttendances(Request $request)
    {
        $rows = $this->attendanceReportQuery($request)
            ->with(['kelas.course.studySemester', 'opener', 'records.student.studySemester'])
            ->latest('session_date')
            ->get();

        return Response::streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            // Supaya CSV aman dibuka di Excel.
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, [
                'Tanggal',
                'Semester Mahasiswa',
                'Mata Kuliah',
                'Kode Mata Kuliah',
                'Kelas',
                'Dibuka Oleh',
                'Status Sesi',
                'Mahasiswa',
                'NIM',
                'Status Kehadiran',
                'Waktu Check-in',
            ]);

            foreach ($rows as $attendance) {
                if ($attendance->records->isEmpty()) {
                    fputcsv($handle, [
                        optional($attendance->session_date)->format('d/m/Y'),
                        $attendance->kelas?->course?->studySemester?->name,
                        $attendance->kelas?->course?->name,
                        $attendance->kelas?->course?->code,
                        $attendance->kelas?->name,
                        $attendance->opener?->name,
                        $attendance->is_open ? 'Terbuka' : 'Ditutup',
                        '-',
                        '-',
                        '-',
                        '-',
                    ]);

                    continue;
                }

                foreach ($attendance->records as $record) {
                    fputcsv($handle, [
                        optional($attendance->session_date)->format('d/m/Y'),
                        $attendance->kelas?->course?->studySemester?->name,
                        $attendance->kelas?->course?->name,
                        $attendance->kelas?->course?->code,
                        $attendance->kelas?->name,
                        $attendance->opener?->name,
                        $attendance->is_open ? 'Terbuka' : 'Ditutup',
                        $record->student?->name,
                        $record->student?->nim_nip,
                        $record->status,
                        optional($record->checked_at)->format('d/m/Y H:i'),
                    ]);
                }
            }

            fclose($handle);
        }, 'laporan-absensi.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function scoreReportQuery(Request $request)
    {
        return Submission::query()
            ->with([
                'student.studySemester',
                'assignment.kelas.course.studySemester',
            ])
            ->when($request->filled('study_semester_id'), function ($query) use ($request) {
                $query->whereHas('assignment.kelas.course', function ($courseQuery) use ($request) {
                    $courseQuery->where('study_semester_id', $request->integer('study_semester_id'));
                });
            })
            ->when($request->filled('course_id'), function ($query) use ($request) {
                $query->whereHas('assignment.kelas', function ($classQuery) use ($request) {
                    $classQuery->where('course_id', $request->integer('course_id'));
                });
            })
            ->when($request->filled('class_id'), function ($query) use ($request) {
                $query->whereHas('assignment', function ($assignmentQuery) use ($request) {
                    $assignmentQuery->where('class_id', $request->integer('class_id'));
                });
            })
            ->when($request->input('grading_status') === 'graded', function ($query) {
                $query->whereNotNull('graded_at');
            })
            ->when($request->input('grading_status') === 'ungraded', function ($query) {
                $query->whereNull('graded_at');
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('submitted_at', '>=', $request->input('date_from'));
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('submitted_at', '<=', $request->input('date_to'));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));
                $operator = $this->caseInsensitiveLikeOperator();
                $term = $this->likeSearchTerm($search);

                $query->where(function ($query) use ($operator, $term) {
                    $query->whereHas('student', function ($studentQuery) use ($operator, $term) {
                        $studentQuery->where('name', $operator, $term)
                            ->orWhere('nim_nip', $operator, $term)
                            ->orWhere('email', $operator, $term);
                    })
                    ->orWhereHas('assignment', function ($assignmentQuery) use ($operator, $term) {
                        $assignmentQuery->where('title', $operator, $term);
                    })
                    ->orWhereHas('assignment.kelas.course', function ($courseQuery) use ($operator, $term) {
                        $courseQuery->where('name', $operator, $term)
                            ->orWhere('code', $operator, $term);
                    });
                });
            });
    }

    private function attendanceReportQuery(Request $request)
    {
        return Attendance::query()
            ->with(['kelas.course.studySemester', 'opener'])
            ->when($request->filled('study_semester_id'), function ($query) use ($request) {
                $query->whereHas('kelas.course', function ($courseQuery) use ($request) {
                    $courseQuery->where('study_semester_id', $request->integer('study_semester_id'));
                });
            })
            ->when($request->filled('course_id'), function ($query) use ($request) {
                $query->whereHas('kelas', function ($classQuery) use ($request) {
                    $classQuery->where('course_id', $request->integer('course_id'));
                });
            })
            ->when($request->filled('class_id'), function ($query) use ($request) {
                $query->where('class_id', $request->integer('class_id'));
            })
            ->when($request->input('session_status') === 'open', function ($query) {
                $query->where('is_open', true);
            })
            ->when($request->input('session_status') === 'closed', function ($query) {
                $query->where('is_open', false);
            })
            ->when($request->filled('record_status'), function ($query) use ($request) {
                $query->whereHas('records', function ($recordQuery) use ($request) {
                    $recordQuery->where('status', $request->input('record_status'));
                });
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('session_date', '>=', $request->input('date_from'));
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('session_date', '<=', $request->input('date_to'));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));
                $operator = $this->caseInsensitiveLikeOperator();
                $term = $this->likeSearchTerm($search);

                $query->where(function ($query) use ($operator, $term) {
                    $query->whereHas('kelas', function ($classQuery) use ($operator, $term) {
                        $classQuery->where('name', $operator, $term);
                    })
                    ->orWhereHas('kelas.course', function ($courseQuery) use ($operator, $term) {
                        $courseQuery->where('name', $operator, $term)
                            ->orWhere('code', $operator, $term);
                    })
                    ->orWhereHas('opener', function ($openerQuery) use ($operator, $term) {
                        $openerQuery->where('name', $operator, $term);
                    });
                });
            });
    }

    private function activityReportQuery(Request $request)
    {
        return LmsNotification::query()
            ->lmsRows()
            ->with(['user.roles'])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));
                $operator = $this->caseInsensitiveLikeOperator();
                $term = $this->likeSearchTerm($search);

                $query->where(function ($query) use ($operator, $term) {
                    $query->where('title', $operator, $term)
                        ->orWhere('message', $operator, $term)
                        ->orWhere('type', $operator, $term)
                        ->orWhereHas('user', function ($userQuery) use ($operator, $term) {
                            $userQuery->where('name', $operator, $term)
                                ->orWhere('email', $operator, $term)
                                ->orWhere('nim_nip', $operator, $term);
                        });
                });
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->where('type', $request->input('type'));
            })
            ->when($request->input('read_status') === 'read', function ($query) {
                $query->whereNotNull('read_at');
            })
            ->when($request->input('read_status') === 'unread', function ($query) {
                $query->whereNull('read_at');
            })
            ->when(in_array($request->input('role'), ['asisten', 'mahasiswa'], true), function ($query) use ($request) {
                $role = $request->input('role');

                $query->whereHas('user', function ($userQuery) use ($role) {
                    $userQuery->where('role', $role)
                        ->orWhereHas('roles', function ($roleQuery) use ($role) {
                            $roleQuery->where('name', $role);
                        });
                });
            })
            ->when($request->filled('date_from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->input('date_from'));
            })
            ->when($request->filled('date_to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->input('date_to'));
            });
    }
}