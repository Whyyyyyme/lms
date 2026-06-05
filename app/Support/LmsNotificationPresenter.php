<?php

namespace App\Support;

use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\LmsNotification;
use App\Models\Material;
use App\Models\PraktikumClass;
use App\Models\Submission;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class LmsNotificationPresenter
{
    public static function displayData(LmsNotification $notification): array
    {
        $data = self::normalizeData($notification->data);
        $class = self::resolveClass($data);

        if ($class instanceof PraktikumClass) {
            $class->loadMissing(['course.studySemester', 'course.academicYear']);
            $course = $class->course;

            $data['class_id'] = $data['class_id'] ?? $class->id;
            $data['class_name'] = filled($data['class_name'] ?? null)
                ? $data['class_name']
                : $class->name;

            if ($course) {
                $data['course_id'] = $data['course_id'] ?? $course->id;
                $data['course_name'] = filled($data['course_name'] ?? null)
                    ? $data['course_name']
                    : $course->name;
                $data['course_code'] = filled($data['course_code'] ?? null)
                    ? $data['course_code']
                    : $course->code;
                $data['study_semester_name'] = filled($data['study_semester_name'] ?? null)
                    ? $data['study_semester_name']
                    : $course->studySemester?->name;
                $data['academic_year_name'] = filled($data['academic_year_name'] ?? null)
                    ? $data['academic_year_name']
                    : $course->academicYear?->name;
                $data['academic_year_active'] = $data['academic_year_active']
                    ?? $course->academicYear?->is_active;
            }
        }

        $data['context_label'] = filled($data['context_label'] ?? null)
            ? $data['context_label']
            : self::contextLabel($data);

        $data['deadline_label'] = self::dateTimeLabel($data['deadline'] ?? null)
            ?? ($data['deadline_label'] ?? null);

        $data['session_date_label'] = self::dateLabel($data['session_date'] ?? null)
            ?? ($data['session_date_label'] ?? null);

        $data['opened_at_label'] = self::dateTimeLabel($data['opened_at'] ?? null)
            ?? ($data['opened_at_label'] ?? null);

        $data['closed_at_label'] = self::dateTimeLabel($data['closed_at'] ?? null)
            ?? ($data['closed_at_label'] ?? null);

        $data['score_label'] = self::scoreLabel($data);
        $data['type_label'] = self::typeLabel($notification);
        $data['type_tone'] = self::typeTone($notification);

        return $data;
    }

    private static function normalizeData(mixed $data): array
    {
        if (! is_array($data)) {
            return [];
        }

        /**
         * Beberapa notifikasi Laravel lama bisa menyimpan payload utama di key "data".
         * Kita ratakan agar Blade cukup membaca satu bentuk array yang aman.
         */
        if (isset($data['data']) && is_array($data['data'])) {
            $nested = $data['data'];
            unset($data['data']);

            $data = array_merge($nested, $data);
        }

        return $data;
    }

    private static function resolveClass(array $data): ?PraktikumClass
    {
        if (filled($data['class_id'] ?? null)) {
            return PraktikumClass::query()
                ->with(['course.studySemester', 'course.academicYear'])
                ->find((int) $data['class_id']);
        }

        if (filled($data['assignment_id'] ?? null)) {
            return Assignment::query()
                ->with(['kelas.course.studySemester', 'kelas.course.academicYear'])
                ->find((int) $data['assignment_id'])
                ?->kelas;
        }

        if (filled($data['material_id'] ?? null)) {
            return Material::query()
                ->with(['kelas.course.studySemester', 'kelas.course.academicYear'])
                ->find((int) $data['material_id'])
                ?->kelas;
        }

        if (filled($data['attendance_id'] ?? null)) {
            return Attendance::query()
                ->with(['kelas.course.studySemester', 'kelas.course.academicYear'])
                ->find((int) $data['attendance_id'])
                ?->kelas;
        }

        if (filled($data['submission_id'] ?? null)) {
            return Submission::query()
                ->with(['assignment.kelas.course.studySemester', 'assignment.kelas.course.academicYear'])
                ->find((int) $data['submission_id'])
                ?->assignment
                ?->kelas;
        }

        if (filled($data['announcement_id'] ?? null)) {
            return Announcement::query()
                ->with(['kelas.course.studySemester', 'kelas.course.academicYear'])
                ->find((int) $data['announcement_id'])
                ?->kelas;
        }

        return null;
    }

    private static function contextLabel(array $data): ?string
    {
        $courseName = $data['course_name'] ?? null;
        $courseCode = $data['course_code'] ?? null;
        $className = $data['class_name'] ?? null;

        if (blank($courseName) && blank($className)) {
            return null;
        }

        $courseLabel = trim((filled($courseCode) ? $courseCode . ' - ' : '') . ($courseName ?? ''));

        if (filled($courseLabel) && filled($className)) {
            return $courseLabel . ' · ' . $className;
        }

        return filled($courseLabel) ? $courseLabel : $className;
    }

    private static function dateTimeLabel(mixed $value): ?string
    {
        $date = self::parseDate($value);

        if (! $date) {
            return filled($value) ? (string) $value : null;
        }

        return $date
            ->timezone(config('app.timezone', 'Asia/Jakarta'))
            ->translatedFormat('d M Y H:i') . ' WIB';
    }

    private static function dateLabel(mixed $value): ?string
    {
        $date = self::parseDate($value);

        if (! $date) {
            return filled($value) ? (string) $value : null;
        }

        return $date
            ->timezone(config('app.timezone', 'Asia/Jakarta'))
            ->translatedFormat('d M Y');
    }

    private static function parseDate(mixed $value): ?CarbonInterface
    {
        if ($value instanceof CarbonInterface) {
            return $value;
        }

        if (blank($value)) {
            return null;
        }

        if (is_string($value) && Str::contains($value, 'WIB')) {
            return null;
        }

        try {
            return Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function scoreLabel(array $data): ?string
    {
        if (! array_key_exists('score', $data) || $data['score'] === null || $data['score'] === '') {
            return null;
        }

        $score = is_numeric($data['score'])
            ? number_format((float) $data['score'], 2, ',', '.')
            : (string) $data['score'];

        $maxScore = $data['max_score'] ?? null;

        if ($maxScore === null || $maxScore === '') {
            return $score;
        }

        $maxScore = is_numeric($maxScore)
            ? number_format((float) $maxScore, 0, ',', '.')
            : (string) $maxScore;

        return $score . '/' . $maxScore;
    }

    private static function typeLabel(LmsNotification $notification): string
    {
        $type = strtolower((string) $notification->type);
        $title = strtolower((string) $notification->title);
        $message = strtolower((string) $notification->message);

        if (self::containsAny($type, $title, $message, ['attendance', 'absensi'])) {
            return 'Absensi';
        }

        if (self::containsAny($type, $title, $message, ['assignment', 'deadline', 'tugas'])) {
            return 'Tugas';
        }

        if (self::containsAny($type, $title, $message, ['material', 'materi'])) {
            return 'Materi';
        }

        if (self::containsAny($type, $title, $message, ['grade', 'nilai'])) {
            return 'Nilai';
        }

        if (self::containsAny($type, $title, $message, ['announcement', 'pengumuman'])) {
            return 'Pengumuman';
        }

        return 'LMS';
    }

    private static function typeTone(LmsNotification $notification): string
    {
        return match (self::typeLabel($notification)) {
            'Absensi' => 'emerald',
            'Tugas' => 'amber',
            'Materi' => 'indigo',
            'Nilai' => 'blue',
            'Pengumuman' => 'violet',
            default => 'slate',
        };
    }

    private static function containsAny(string $type, string $title, string $message, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (
                str_contains($type, $needle) ||
                str_contains($title, $needle) ||
                str_contains($message, $needle)
            ) {
                return true;
            }
        }

        return false;
    }
}
