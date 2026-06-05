<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\Material;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MaterialController extends Controller
{
    use ResolvesClassAccess;

    public function index(): View
    {
        $classIds = $this->studentClassIds();
        $courseIds = $this->studentClasses()
            ->pluck('course_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $courses = $this->buildCourseMaterialCards($courseIds, $classIds);

        return view('student.materials.index', [
            'courses' => $courses,
            'archivedClassesCount' => count($this->studentArchivedClassIds()),
        ]);
    }

    public function history(): View
    {
        $classIds = $this->studentArchivedClassIds();
        $courseIds = $this->studentArchivedClasses()
            ->pluck('course_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $courses = $this->buildCourseMaterialCards($courseIds, $classIds);

        return view('student.materials.history', compact('courses'));
    }

    public function course(Course $course): View
    {
        $classIds = $this->studentClassIds();

        $allowedCourseIds = $this->studentClasses()
            ->pluck('course_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        abort_unless(in_array((int) $course->id, $allowedCourseIds, true), 403);

        $materials = Material::query()
            ->with(['kelas.course', 'creator'])
            ->published()
            ->whereIn('class_id', $classIds)
            ->whereHas('kelas', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->latest('published_at')
            ->paginate(10);

        $course->loadMissing(['studySemester', 'academicYear']);
        $isHistoryTab = false;

        return view('student.materials.course', compact('course', 'materials', 'isHistoryTab'));
    }

    public function historyCourse(Course $course): View
    {
        $classIds = $this->studentArchivedClassIds();

        $allowedCourseIds = $this->studentArchivedClasses()
            ->pluck('course_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        abort_unless(in_array((int) $course->id, $allowedCourseIds, true), 403);

        $materials = Material::query()
            ->with(['kelas.course', 'creator'])
            ->published()
            ->whereIn('class_id', $classIds)
            ->whereHas('kelas', function ($query) use ($course) {
                $query->where('course_id', $course->id);
            })
            ->latest('published_at')
            ->paginate(10);

        $course->loadMissing(['studySemester', 'academicYear']);
        $isHistoryTab = true;

        return view('student.materials.course', compact('course', 'materials', 'isHistoryTab'));
    }

    private function buildCourseMaterialCards($courseIds, array $classIds)
    {
        $materialStats = collect();

        if ($classIds !== []) {
            $materialStats = Material::query()
                ->published()
                ->join('classes', 'materials.class_id', '=', 'classes.id')
                ->whereIn('materials.class_id', $classIds)
                ->selectRaw('classes.course_id, COUNT(materials.id) as total_materials, MAX(materials.published_at) as latest_material_at')
                ->groupBy('classes.course_id')
                ->get()
                ->keyBy('course_id');
        }

        return Course::query()
            ->with(['studySemester', 'academicYear'])
            ->whereIn('id', $courseIds)
            ->orderBy('name')
            ->get()
            ->map(function (Course $course) use ($materialStats) {
                $stats = $materialStats->get($course->id);

                $course->setAttribute('materials_count', (int) ($stats->total_materials ?? 0));
                $course->setAttribute('latest_material_at', $stats->latest_material_at ?? null);

                return $course;
            });
    }

    public function show(Material $material): View
    {
        abort_unless(in_array((int) $material->class_id, $this->studentAllClassIds(), true), 403);
        abort_if($material->published_at === null || $material->published_at->isFuture(), 404);

        $material->load(['kelas.course', 'creator']);

        $viewer = $this->buildViewerData($material);

        return view('student.materials.show', compact('material', 'viewer'));
    }

    public function preview(Material $material)
    {
        abort_unless(in_array((int) $material->class_id, $this->studentAllClassIds(), true), 403);
        abort_if($material->published_at === null || $material->published_at->isFuture(), 404);

        $source = $this->materialSource($material);

        abort_if(blank($source), 404);

        if ($this->isExternalUrl($source)) {
            return redirect()->away($source);
        }

        abort_unless(Storage::disk('public')->exists($source), 404);

        $path = Storage::disk('public')->path($source);
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $allowedPreviewExtensions = [
            'pdf',
            'mp4',
            'webm',
            'ogg',
            'mov',
            'm4v',
        ];

        abort_unless(in_array($extension, $allowedPreviewExtensions, true), 404);

        $mime = match ($extension) {
            'pdf' => 'application/pdf',
            'mp4', 'm4v' => 'video/mp4',
            'webm' => 'video/webm',
            'ogg' => 'video/ogg',
            'mov' => 'video/quicktime',
            default => mime_content_type($path) ?: 'application/octet-stream',
        };

        return response()->file($path, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="'.basename($path).'"',
        ]);
    }

    public function download(Material $material)
    {
        abort_unless(in_array((int) $material->class_id, $this->studentAllClassIds(), true), 403);
        abort_if($material->published_at === null || $material->published_at->isFuture(), 404);
        abort_if(blank($this->materialSource($material)), 404);

        $source = $this->materialSource($material);

        if ($this->isExternalUrl($source)) {
            return redirect()->away($source);
        }

        abort_unless(Storage::disk('public')->exists($source), 404);

        return Storage::disk('public')->download($source);
    }

    private function buildViewerData(Material $material): array
    {
        $source = $this->materialSource($material);

        $viewer = [
            'type' => 'empty',
            'url' => null,
            'embed_url' => null,
            'download_url' => null,
            'message' => 'Materi ini belum memiliki file atau link.',
        ];

        if (blank($source)) {
            return $viewer;
        }

        $embedUrl = $this->videoEmbedUrl($source);

        if ($embedUrl) {
            return [
                'type' => 'video_iframe',
                'url' => $source,
                'embed_url' => $embedUrl,
                'download_url' => null,
                'message' => null,
            ];
        }

        if ($this->isVideoFile($source)) {
            return [
                'type' => 'video_file',
                'url' => $source,
                'embed_url' => $this->isExternalUrl($source)
                    ? $source
                    : route('student.materials.preview', $material),
                'download_url' => $this->isExternalUrl($source)
                    ? $source
                    : route('student.materials.download', $material),
                'message' => null,
            ];
        }

        if ($this->isPdf($source)) {
            return [
                'type' => 'pdf',
                'url' => $source,
                'embed_url' => $this->isExternalUrl($source)
                    ? $source
                    : route('student.materials.preview', $material),
                'download_url' => $this->isExternalUrl($source)
                    ? $source
                    : route('student.materials.download', $material),
                'message' => null,
            ];
        }

        if ($this->isExternalUrl($source)) {
            return [
                'type' => 'link',
                'url' => $source,
                'embed_url' => null,
                'download_url' => $source,
                'message' => 'Materi ini berupa link eksternal. Link ini tidak bisa ditampilkan langsung sebagai video/PDF.',
            ];
        }

        return [
            'type' => 'file',
            'url' => $source,
            'embed_url' => null,
            'download_url' => route('student.materials.download', $material),
            'message' => 'File ini tidak bisa dibaca langsung di browser. Silakan unduh file.',
        ];
    }

    private function materialSource(Material $material): ?string
    {
        return $material->file_path
            ?? $material->file_url
            ?? $material->url
            ?? $material->link
            ?? $material->external_url
            ?? null;
    }

    private function isExternalUrl(string $source): bool
    {
        return str_starts_with($source, 'http://') || str_starts_with($source, 'https://');
    }

    private function isPdf(string $source): bool
    {
        $path = parse_url($source, PHP_URL_PATH) ?: $source;

        return strtolower(pathinfo($path, PATHINFO_EXTENSION)) === 'pdf';
    }

    private function isVideoFile(string $source): bool
    {
        $path = parse_url($source, PHP_URL_PATH) ?: $source;
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['mp4', 'webm', 'ogg', 'mov', 'm4v'], true);
    }

    private function videoEmbedUrl(string $url): ?string
    {
        if (! $this->isExternalUrl($url)) {
            return null;
        }

        return $this->youtubeEmbedUrl($url)
            ?? $this->googleDriveEmbedUrl($url)
            ?? $this->vimeoEmbedUrl($url)
            ?? $this->loomEmbedUrl($url);
    }

    private function youtubeEmbedUrl(string $url): ?string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
        $path = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');
        $query = parse_url($url, PHP_URL_QUERY) ?? '';

        $videoId = null;

        if (str_contains($host, 'youtu.be')) {
            $videoId = explode('/', $path)[0] ?? null;
        }

        if (str_contains($host, 'youtube.com')) {
            parse_str($query, $queryParams);

            if (! empty($queryParams['v'])) {
                $videoId = $queryParams['v'];
            }

            if (str_starts_with($path, 'shorts/')) {
                $videoId = str_replace('shorts/', '', $path);
            }

            if (str_starts_with($path, 'embed/')) {
                $videoId = str_replace('embed/', '', $path);
            }

            if (str_starts_with($path, 'live/')) {
                $videoId = str_replace('live/', '', $path);
            }
        }

        if (blank($videoId)) {
            return null;
        }

        $videoId = preg_replace('/[^a-zA-Z0-9_-]/', '', $videoId);

        if (blank($videoId)) {
            return null;
        }

        return 'https://www.youtube.com/embed/'.$videoId;
    }

    private function googleDriveEmbedUrl(string $url): ?string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
        $path = parse_url($url, PHP_URL_PATH) ?? '';
        $query = parse_url($url, PHP_URL_QUERY) ?? '';

        if (! str_contains($host, 'drive.google.com')) {
            return null;
        }

        $fileId = null;

        if (preg_match('#/file/d/([^/]+)#', $path, $matches)) {
            $fileId = $matches[1];
        }

        if (! $fileId) {
            parse_str($query, $queryParams);

            if (! empty($queryParams['id'])) {
                $fileId = $queryParams['id'];
            }
        }

        if (blank($fileId)) {
            return null;
        }

        $fileId = preg_replace('/[^a-zA-Z0-9_-]/', '', $fileId);

        if (blank($fileId)) {
            return null;
        }

        return 'https://drive.google.com/file/d/'.$fileId.'/preview';
    }

    private function vimeoEmbedUrl(string $url): ?string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
        $path = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');

        if (! str_contains($host, 'vimeo.com')) {
            return null;
        }

        if (str_contains($host, 'player.vimeo.com')) {
            return $url;
        }

        $parts = explode('/', $path);
        $videoId = end($parts);

        if (blank($videoId)) {
            return null;
        }

        $videoId = preg_replace('/[^0-9]/', '', $videoId);

        if (blank($videoId)) {
            return null;
        }

        return 'https://player.vimeo.com/video/'.$videoId;
    }

    private function loomEmbedUrl(string $url): ?string
    {
        $host = strtolower(parse_url($url, PHP_URL_HOST) ?? '');
        $path = trim(parse_url($url, PHP_URL_PATH) ?? '', '/');

        if (! str_contains($host, 'loom.com')) {
            return null;
        }

        if (str_starts_with($path, 'embed/')) {
            return $url;
        }

        if (! str_starts_with($path, 'share/')) {
            return null;
        }

        $videoId = str_replace('share/', '', $path);
        $videoId = preg_replace('/[^a-zA-Z0-9_-]/', '', $videoId);

        if (blank($videoId)) {
            return null;
        }

        return 'https://www.loom.com/embed/'.$videoId;
    }
}
