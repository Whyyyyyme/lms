<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Course;
use App\Models\StudySemester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $status = (string) $request->input('status', '');

        $courses = Course::query()
            ->with(['academicYear', 'studySemester'])
            ->withCount('classes')
            ->when($request->filled('study_semester_id'), function ($query) use ($request) {
                $query->where('study_semester_id', $request->integer('study_semester_id'));
            })
            ->when($request->filled('academic_year_id'), function ($query) use ($request) {
                $query->where('academic_year_id', $request->integer('academic_year_id'));
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('is_active', $status === '1');
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('admin.courses.index', [
            'courses' => $courses,
            'academicYears' => AcademicYear::orderByDesc('is_active')->latest()->get(),
            'studySemesters' => StudySemester::orderBy('level')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.courses.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'study_semester_id' => ['required', 'exists:study_semesters,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:courses,code'],
            'sks' => ['required', 'integer', 'min:1', 'max:6'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $course = Course::create($validated);

        return redirect()
            ->route('admin.matakuliah.show', $course)
            ->with('success', 'Mata kuliah berhasil ditambahkan.');
    }

    public function show(Course $course): View
    {
        $course->load([
            'academicYear',
            'studySemester.students',
            'classes.assistant',
            'classes.students',
        ]);

        $course->loadCount('classes');

        return view('admin.courses.show', compact('course'));
    }

    public function edit(Course $course): View
    {
        return view('admin.courses.edit', array_merge($this->formData(), [
            'course' => $course,
        ]));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'study_semester_id' => ['required', 'exists:study_semesters,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('courses', 'code')->ignore($course->id)],
            'sks' => ['required', 'integer', 'min:1', 'max:6'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $course->update($validated);

        return redirect()
            ->route('admin.matakuliah.show', $course)
            ->with('success', 'Mata kuliah berhasil diperbarui.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        abort_if(
            $course->classes()->exists(),
            422,
            'Mata kuliah tidak bisa dihapus karena masih memiliki kelas praktikum.'
        );

        $course->delete();

        return redirect()
            ->route('admin.matakuliah.index')
            ->with('success', 'Mata kuliah berhasil dihapus.');
    }

    private function formData(): array
    {
        return [
            'academicYears' => AcademicYear::query()
                ->orderByDesc('is_active')
                ->latest()
                ->get(),

            'studySemesters' => StudySemester::query()
                ->active()
                ->orderBy('level')
                ->get(),
        ];
    }
}