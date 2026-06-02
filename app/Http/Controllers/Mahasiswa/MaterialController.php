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

        $courses = Course::query()
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

        return view('student.materials.index', compact('courses'));
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

        return view('student.materials.course', compact('course', 'materials'));
    }

    public function show(Material $material): View
    {
        abort_unless(in_array($material->class_id, $this->studentClassIds(), true), 403);
        abort_if($material->published_at === null || $material->published_at->isFuture(), 404);

        $material->load(['kelas.course', 'creator']);

        return view('student.materials.show', compact('material'));
    }

    public function download(Material $material)
    {
        abort_unless(in_array($material->class_id, $this->studentClassIds(), true), 403);
        abort_if(blank($material->file_path), 404);

        if (str_starts_with($material->file_path, 'http')) {
            return redirect()->away($material->file_path);
        }

        abort_unless(Storage::disk('public')->exists($material->file_path), 404);

        return Storage::disk('public')->download($material->file_path);
    }
}
