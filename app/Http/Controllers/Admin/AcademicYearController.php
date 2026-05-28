<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AcademicYearController extends Controller
{
    public function index(): View
    {
        $academicYears = AcademicYear::query()
            ->withCount('courses')
            ->latest()
            ->paginate(10);

        return view('admin.academic-years.index', compact('academicYears'));
    }

    public function create(): View
    {
        return view('admin.academic-years.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'year' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_active')) {
            AcademicYear::query()->update(['is_active' => false]);
        }

        AcademicYear::create([
            'year' => $validated['year'],
            'semester' => $validated['semester'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.tahun-akademik.index')->with('success', 'Tahun akademik berhasil ditambahkan.');
    }

    public function show(AcademicYear $academicYear): View
    {
        $academicYear->load(['courses.classes.assistant']);

        return view('admin.academic-years.show', compact('academicYear'));
    }

    public function edit(AcademicYear $academicYear): View
    {
        return view('admin.academic-years.edit', compact('academicYear'));
    }

    public function update(Request $request, AcademicYear $academicYear): RedirectResponse
    {
        $validated = $request->validate([
            'year' => ['required', 'string', 'max:20'],
            'semester' => ['required', Rule::in(['ganjil', 'genap'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_active')) {
            AcademicYear::whereKeyNot($academicYear->id)->update(['is_active' => false]);
        }

        $academicYear->update([
            'year' => $validated['year'],
            'semester' => $validated['semester'],
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.tahun-akademik.index')->with('success', 'Tahun akademik berhasil diperbarui.');
    }

    public function destroy(AcademicYear $academicYear): RedirectResponse
    {
        abort_if($academicYear->courses()->exists(), 422, 'Tahun akademik masih memiliki matakuliah.');

        $academicYear->delete();

        return redirect()->route('admin.tahun-akademik.index')->with('success', 'Tahun akademik berhasil dihapus.');
    }
}
