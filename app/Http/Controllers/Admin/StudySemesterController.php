<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\UsesCaseInsensitiveSearch;
use App\Models\StudySemester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudySemesterController extends Controller
{
    use UsesCaseInsensitiveSearch;

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));

        $studySemesters = StudySemester::query()
            ->withCount(['courses', 'students', 'enrollments'])
            ->when($search !== '', function ($query) use ($search) {
                $operator = $this->caseInsensitiveLikeOperator();
                $term = $this->likeSearchTerm($search);

                $query->where(function ($query) use ($operator, $term, $search) {
                    $query->where('name', $operator, $term)
                        ->orWhere('description', $operator, $term)
                        ->orWhere('level', $search);
                });
            })
            ->orderBy('level')
            ->paginate(10)
            ->withQueryString();

        return view('admin.study-semesters.index', compact('studySemesters'));
    }

    public function create(): View
    {
        return view('admin.study-semesters.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'level' => ['required', 'integer', 'min:1', 'max:8', 'unique:study_semesters,level'],
            'name' => ['required', 'string', 'max:100', 'unique:study_semesters,name'],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        StudySemester::create($validated);

        return redirect()
            ->route('admin.semester.index')
            ->with('success', 'Semester mahasiswa berhasil ditambahkan.');
    }

    public function show(StudySemester $studySemester): View
    {
        $studySemester->load([
            'courses' => function ($query) {
                $query->with(['academicYear', 'classes.assistant'])
                    ->orderBy('name');
            },
            'students' => function ($query) {
                $query->where('role', 'mahasiswa')
                    ->orderBy('name');
            },
        ]);

        $studySemester->loadCount(['courses', 'students', 'enrollments']);

        return view('admin.study-semesters.show', compact('studySemester'));
    }

    public function edit(StudySemester $studySemester): View
    {
        return view('admin.study-semesters.edit', compact('studySemester'));
    }

    public function update(Request $request, StudySemester $studySemester): RedirectResponse
    {
        $validated = $request->validate([
            'level' => [
                'required',
                'integer',
                'min:1',
                'max:8',
                Rule::unique('study_semesters', 'level')->ignore($studySemester->id),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('study_semesters', 'name')->ignore($studySemester->id),
            ],
            'description' => ['nullable', 'string'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $studySemester->update($validated);

        return redirect()
            ->route('admin.semester.show', $studySemester)
            ->with('success', 'Semester mahasiswa berhasil diperbarui.');
    }

    public function destroy(StudySemester $studySemester): RedirectResponse
    {
        abort_if(
            $studySemester->courses()->exists()
            || $studySemester->students()->exists()
            || $studySemester->enrollments()->exists(),
            422,
            'Semester tidak bisa dihapus karena masih dipakai oleh mata kuliah, mahasiswa, atau riwayat enrollment.'
        );

        $studySemester->delete();

        return redirect()
            ->route('admin.semester.index')
            ->with('success', 'Semester mahasiswa berhasil dihapus.');
    }
}