<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\Course;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $courses = Course::query()
            ->with('academicYear')
            ->withCount('classes')
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->string('search');
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.courses.index', compact('courses'));
    }

    public function create(): View
    {
        return view('admin.courses.create', [
            'academicYears' => AcademicYear::orderByDesc('is_active')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', 'unique:courses,code'],
            'sks' => ['required', 'integer', 'min:1', 'max:6'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        Course::create($validated);

        return redirect()->route('admin.matakuliah.index')->with('success', 'Matakuliah berhasil ditambahkan.');
    }

    public function show(Course $course): View
    {
        $course->load(['academicYear', 'classes.assistant', 'classes.students']);

        return view('admin.courses.show', compact('course'));
    }

    public function edit(Course $course): View
    {
        return view('admin.courses.edit', [
            'course' => $course,
            'academicYears' => AcademicYear::orderByDesc('is_active')->latest()->get(),
        ]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'academic_year_id' => ['required', 'exists:academic_years,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:50', Rule::unique('courses', 'code')->ignore($course->id)],
            'sks' => ['required', 'integer', 'min:1', 'max:6'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $course->update($validated);

        return redirect()->route('admin.matakuliah.index')->with('success', 'Matakuliah berhasil diperbarui.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        abort_if($course->classes()->exists(), 422, 'Matakuliah masih memiliki kelas praktikum.');

        $course->delete();

        return redirect()->route('admin.matakuliah.index')->with('success', 'Matakuliah berhasil dihapus.');
    }
}
