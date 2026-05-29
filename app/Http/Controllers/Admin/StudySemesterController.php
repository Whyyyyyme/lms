<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudySemester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudySemesterController extends Controller
{
    public function index(): View
    {
        $studySemesters = StudySemester::query()
            ->withCount(['courses', 'students'])
            ->orderBy('level')
            ->paginate(10);

        return view('admin.study-semesters.index', compact('studySemesters'));
    }

    public function create(): View
    {
        return view('admin.study-semesters.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'level' => ['required', 'integer', 'min:1', 'max:14', 'unique:study_semesters,level'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        StudySemester::create($validated);

        return redirect()->route('admin.semester.index')->with('success', 'Semester mahasiswa berhasil ditambahkan.');
    }

    public function show(StudySemester $studySemester): View
    {
        $studySemester->load(['courses.academicYear', 'students']);

        return view('admin.study-semesters.show', compact('studySemester'));
    }

    public function edit(StudySemester $studySemester): View
    {
        return view('admin.study-semesters.edit', compact('studySemester'));
    }

    public function update(Request $request, StudySemester $studySemester): RedirectResponse
    {
        $validated = $request->validate([
            'level' => ['required', 'integer', 'min:1', 'max:14', Rule::unique('study_semesters', 'level')->ignore($studySemester->id)],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $studySemester->update($validated);

        return redirect()->route('admin.semester.index')->with('success', 'Semester mahasiswa berhasil diperbarui.');
    }

    public function destroy(StudySemester $studySemester): RedirectResponse
    {
        abort_if($studySemester->courses()->exists() || $studySemester->students()->exists(), 422, 'Semester masih dipakai oleh matakuliah atau mahasiswa.');

        $studySemester->delete();

        return redirect()->route('admin.semester.index')->with('success', 'Semester mahasiswa berhasil dihapus.');
    }
}
