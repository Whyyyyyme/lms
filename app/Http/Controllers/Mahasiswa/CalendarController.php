<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Assignment;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CalendarController extends Controller
{
    use ResolvesClassAccess;

    public function schedule(Request $request): View
    {
        $month = $this->resolveMonth($request->query('bulan'));
        $previousMonth = $month->copy()->subMonth();
        $nextMonth = $month->copy()->addMonth();

        $allClasses = $this->studentClasses()->load(['course', 'assistant']);

        $courses = $allClasses
            ->pluck('course')
            ->filter()
            ->unique('id')
            ->sortBy('name')
            ->values();

        $selectedCourseId = $request->integer('mata_kuliah') ?: null;
        $validCourseIds = $courses->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($selectedCourseId && in_array($selectedCourseId, $validCourseIds, true)) {
            $classes = $allClasses
                ->filter(fn ($class) => (int) $class->course_id === $selectedCourseId)
                ->values();
        } else {
            $selectedCourseId = null;
            $classes = $allClasses;
        }

        $classIds = $classes
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $calendarStart = $month->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $praktikumEvents = $this->buildPraktikumEvents($classes, $month);

        $assignmentEvents = Assignment::query()
            ->with(['kelas.course'])
            ->published()
            ->whereIn('class_id', $classIds)
            ->whereBetween('deadline', [
                $calendarStart->copy()->startOfDay(),
                $calendarEnd->copy()->endOfDay(),
            ])
            ->get()
            ->map(function (Assignment $assignment) {
                return [
                    'date' => $assignment->deadline?->toDateString(),
                    'sort_time' => $assignment->deadline?->format('H:i') ?? '23:59',
                    'time' => $assignment->deadline?->format('H:i'),
                    'variant' => 'tugas',
                    'badge' => 'Tugas',
                    'title' => $assignment->title,
                    'subtitle' => trim(($assignment->kelas?->course?->name ?? 'Mata kuliah') . ' · ' . ($assignment->kelas?->name ?? '')),
                    'url' => route('student.assignments.show', $assignment),
                ];
            });

        $attendanceEvents = Attendance::query()
            ->with(['kelas.course'])
            ->whereIn('class_id', $classIds)
            ->whereBetween('session_date', [
                $calendarStart->toDateString(),
                $calendarEnd->toDateString(),
            ])
            ->get()
            ->map(function (Attendance $attendance) {
                return [
                    'date' => $attendance->session_date?->toDateString(),
                    'sort_time' => $attendance->opened_at?->format('H:i') ?? '00:01',
                    'time' => $attendance->opened_at?->format('H:i'),
                    'variant' => 'absensi',
                    'badge' => 'Absensi',
                    'title' => $attendance->is_open ? 'Absensi Dibuka' : 'Sesi Absensi',
                    'subtitle' => trim(($attendance->kelas?->course?->name ?? 'Mata kuliah') . ' · ' . ($attendance->kelas?->name ?? '')),
                    'url' => route('student.attendances.index'),
                ];
            });

        $events = collect()
            ->merge($praktikumEvents)
            ->merge($assignmentEvents)
            ->merge($attendanceEvents)
            ->filter(fn ($event) => filled($event['date'] ?? null))
            ->sortBy([
                ['date', 'asc'],
                ['sort_time', 'asc'],
            ])
            ->values();

        $eventsByDate = $events->groupBy('date');
        $weeks = $this->buildCalendarWeeks($month, $eventsByDate);

        $upcomingEvents = $events
            ->filter(fn ($event) => $event['date'] >= now()->toDateString())
            ->take(8)
            ->values();

        return view('student.schedule.index', compact(
            'month',
            'previousMonth',
            'nextMonth',
            'courses',
            'selectedCourseId',
            'weeks',
            'upcomingEvents'
        ));
    }

    public function index(): View
    {
        $classIds = $this->studentClassIds();

        $assignments = Assignment::with('kelas.course')
            ->published()
            ->whereIn('class_id', $classIds)
            ->orderBy('deadline')
            ->get();

        $attendances = Attendance::with('kelas.course')
            ->whereIn('class_id', $classIds)
            ->orderBy('session_date')
            ->get();

        $activeAcademicYear = AcademicYear::where('is_active', true)->first();

        return view('student.calendar.index', compact('assignments', 'attendances', 'activeAcademicYear'));
    }

    private function resolveMonth(?string $value): Carbon
    {
        if (filled($value)) {
            try {
                return Carbon::createFromFormat('Y-m-d', $value . '-01')->startOfMonth();
            } catch (\Throwable) {
                return now()->startOfMonth();
            }
        }

        return now()->startOfMonth();
    }

    private function buildCalendarWeeks(Carbon $month, Collection $eventsByDate): array
    {
        $calendarStart = $month->copy()->startOfMonth()->startOfWeek(Carbon::MONDAY);
        $calendarEnd = $month->copy()->endOfMonth()->endOfWeek(Carbon::SUNDAY);

        $weeks = [];
        $cursor = $calendarStart->copy();

        while ($cursor->lte($calendarEnd)) {
            $week = [];

            for ($i = 0; $i < 7; $i++) {
                $dateKey = $cursor->toDateString();

                $week[] = [
                    'date' => $cursor->copy(),
                    'date_key' => $dateKey,
                    'is_current_month' => $cursor->isSameMonth($month),
                    'is_today' => $cursor->isToday(),
                    'events' => $eventsByDate->get($dateKey, collect()),
                ];

                $cursor->addDay();
            }

            $weeks[] = $week;
        }

        return $weeks;
    }

    private function buildPraktikumEvents(Collection $classes, Carbon $month): Collection
    {
        $events = collect();
        $start = $month->copy()->startOfMonth();
        $end = $month->copy()->endOfMonth();

        foreach ($classes as $class) {
            $weekday = $this->weekdayFromSchedule($class->schedule);

            if (! $weekday) {
                continue;
            }

            $time = $this->timeFromSchedule($class->schedule);
            $sortTime = $time ? substr($time, 0, 5) : '00:00';
            $cursor = $start->copy();

            while ((int) $cursor->dayOfWeekIso !== $weekday) {
                $cursor->addDay();
            }

            while ($cursor->lte($end)) {
                $events->push([
                    'date' => $cursor->toDateString(),
                    'sort_time' => $sortTime,
                    'time' => $time,
                    'variant' => 'praktikum',
                    'badge' => 'Praktikum',
                    'title' => $class->course?->name ?? 'Praktikum',
                    'subtitle' => trim(($class->name ?? 'Kelas') . ' · Ruang ' . ($class->room ?: '-') . ' · Asisten ' . ($class->assistant?->name ?? '-')),
                    'url' => null,
                ]);

                $cursor->addWeek();
            }
        }

        return $events;
    }

    private function weekdayFromSchedule(?string $schedule): ?int
    {
        if (blank($schedule)) {
            return null;
        }

        $text = strtolower($schedule);

        $days = [
            'senin' => 1,
            'selasa' => 2,
            'rabu' => 3,
            'kamis' => 4,
            'jumat' => 5,
            "jum'at" => 5,
            'jum’at' => 5,
            'sabtu' => 6,
            'minggu' => 7,
            'ahad' => 7,
        ];

        foreach ($days as $keyword => $dayNumber) {
            if (str_contains($text, $keyword)) {
                return $dayNumber;
            }
        }

        return null;
    }

    private function timeFromSchedule(?string $schedule): ?string
    {
        if (blank($schedule)) {
            return null;
        }

        if (preg_match('/(\d{1,2}[:.]\d{2})\s*(?:-|–|—|sampai|sd|s\/d)\s*(\d{1,2}[:.]\d{2})/i', $schedule, $matches)) {
            return $this->normalizeTime($matches[1]) . ' - ' . $this->normalizeTime($matches[2]);
        }

        if (preg_match('/(\d{1,2}[:.]\d{2})/i', $schedule, $matches)) {
            return $this->normalizeTime($matches[1]);
        }

        return null;
    }

    private function normalizeTime(string $time): string
    {
        $time = str_replace('.', ':', $time);
        [$hour, $minute] = array_pad(explode(':', $time, 2), 2, '00');

        return str_pad($hour, 2, '0', STR_PAD_LEFT) . ':' . str_pad(substr($minute, 0, 2), 2, '0', STR_PAD_RIGHT);
    }
}
