<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\PraktikumClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClassController extends Controller
{
    public function index(Request $request): View
    {
        $classes = PraktikumClass::query()
            ->with(['course.academicYear', 'course.studySemester', 'assistant'])
            ->withCount('students')
            ->when($request->filled('course_id'), fn ($query) => $query->where('course_id', $request->integer('course_id')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.classes.index', compact('classes'));
    }

    public function create(): View
    {
        return view('admin.classes.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'assistant_id' => ['nullable', 'exists:users,id'],
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('classes', 'name')->where(fn ($query) => $query->where('course_id', $request->integer('course_id'))),
            ],
            'room' => ['nullable', 'string', 'max:100'],
            'schedule' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:users,id'],
        ]);

        $studentIds = $validated['student_ids'] ?? [];
        unset($validated['student_ids']);
        $validated['is_active'] = $request->boolean('is_active');

        $class = PraktikumClass::create($validated);
        $this->syncStudents($class, $studentIds);

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas praktikum berhasil ditambahkan.');
    }

    public function show(PraktikumClass $praktikumClass): View
    {
        $praktikumClass->load(['course.academicYear', 'course.studySemester', 'assistant', 'students.studySemester', 'materials', 'assignments']);

        return view('admin.classes.show', compact('praktikumClass'));
    }

    public function edit(PraktikumClass $praktikumClass): View
    {
        return view('admin.classes.edit', array_merge($this->formData(), [
            'praktikumClass' => $praktikumClass->load('students'),
        ]));
    }

    public function update(Request $request, PraktikumClass $praktikumClass): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'assistant_id' => ['nullable', 'exists:users,id'],
            'name' => [
                'required', 'string', 'max:100',
                Rule::unique('classes', 'name')
                    ->where(fn ($query) => $query->where('course_id', $request->integer('course_id')))
                    ->ignore($praktikumClass->id),
            ],
            'room' => ['nullable', 'string', 'max:100'],
            'schedule' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:users,id'],
        ]);

        $studentIds = $validated['student_ids'] ?? [];
        unset($validated['student_ids']);
        $validated['is_active'] = $request->boolean('is_active');

        $praktikumClass->update($validated);
        $this->syncStudents($praktikumClass, $studentIds);

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas praktikum berhasil diperbarui.');
    }

    public function destroy(PraktikumClass $praktikumClass): RedirectResponse
    {
        abort_if($praktikumClass->materials()->exists() || $praktikumClass->assignments()->exists(), 422, 'Kelas masih memiliki materi atau tugas.');

        $praktikumClass->students()->detach();
        $praktikumClass->delete();

        return redirect()->route('admin.kelas.index')->with('success', 'Kelas praktikum berhasil dihapus.');
    }

    private function formData(): array
    {
        return [
            'courses' => Course::with(['academicYear', 'studySemester'])->active()->orderBy('study_semester_id')->orderBy('name')->get(),
            'assistants' => User::role('asisten')->active()->orderBy('name')->get(),
            'students' => User::role('mahasiswa')->with('studySemester')->active()->orderBy('study_semester_id')->orderBy('name')->get(),
        ];
    }

    private function syncStudents(PraktikumClass $class, array $studentIds): void
    {
        $class->students()->sync($studentIds);

        // Tidak mengubah users.kelas_id lagi, karena mahasiswa sekarang boleh mengikuti beberapa kelas/matakuliah dalam satu semester.
        // Akses umum mahasiswa dihitung dari study_semester_id, sedangkan class_students dipakai untuk override/pembagian kelas spesifik.
    }
}
