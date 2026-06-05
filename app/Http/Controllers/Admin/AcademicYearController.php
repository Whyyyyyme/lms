<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\UsesCaseInsensitiveSearch;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    use UsesCaseInsensitiveSearch;

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $status = (string) $request->input('status', '');
        $semester = (string) $request->input('semester', '');

        $academicYears = AcademicYear::query()
            ->withCount('courses')
            ->when($search !== '', function ($query) use ($search) {
                $query->where('year', $this->caseInsensitiveLikeOperator(), $this->likeSearchTerm($search));
            })
            ->when(in_array($semester, ['ganjil', 'genap'], true), function ($query) use ($semester) {
                $query->where('semester', $semester);
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('is_active', $status === '1');
            })
            ->orderByDesc('is_active')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.academic-years.index', compact('academicYears'));
    }

    public function create(): View
    {
        return view('admin.academic-years.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'year' => [
                'required',
                'string',
                'max:20',
                Rule::unique('academic_years', 'year')->where(function ($query) use ($request) {
                    $query->where('semester', $request->input('semester'));
                }),
            ],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_active')) {
            AcademicYear::query()->update(['is_active' => false]);
        }

        $academicYear = AcademicYear::create([
            'year' => $validated['year'],
            'semester' => $validated['semester'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.tahun-akademik.show', $academicYear)
            ->with('success', 'Tahun akademik berhasil ditambahkan.');
    }

    public function show(AcademicYear $academicYear): View
    {
        $academicYear->load([
            'courses' => function ($query) {
                $query->with(['studySemester', 'classes.assistant'])
                    ->withCount('classes')
                    ->orderBy('name');
            },
        ]);

        $academicYear->loadCount('courses');

        return view('admin.academic-years.show', compact('academicYear'));
    }

    public function edit(AcademicYear $academicYear): View
    {
        return view('admin.academic-years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $validated = $request->validate([
            'year' => [
                'required',
                'string',
                'max:20',
                Rule::unique('academic_years', 'year')
                    ->where(function ($query) use ($request) {
                        $query->where('semester', $request->input('semester'));
                    })
                    ->ignore($academicYear->id),
            ],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_active')) {
            AcademicYear::where('id', '!=', $academicYear->id)->update(['is_active' => false]);
        }

        $academicYear->update([
            'year' => $validated['year'],
            'semester' => $validated['semester'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()
            ->route('admin.tahun-akademik.show', $academicYear)
            ->with('success', 'Tahun akademik berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        abort_if(
            $academicYear->courses()->exists(),
            422,
            'Tahun akademik tidak bisa dihapus karena masih dipakai oleh mata kuliah.'
        );

        $academicYear->delete();

        return redirect()
            ->route('admin.tahun-akademik.index')
            ->with('success', 'Tahun akademik berhasil dihapus.');
    }
}