<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\PraktikumClass;
use App\Models\StudySemester;
use App\Models\User;
use App\Services\StudentAccessService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClassController extends Controller
{
    public function __construct(private readonly StudentAccessService $studentAccess)
    {
    }

    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search'));
        $status = (string) $request->input('status', '');

        $classes = PraktikumClass::query()
            ->with(['course.academicYear', 'course.studySemester', 'assistant'])
            ->withCount(['students', 'materials', 'assignments', 'attendances'])
            ->when($request->filled('study_semester_id'), function ($query) use ($request) {
                $query->whereHas('course', function ($courseQuery) use ($request) {
                    $courseQuery->where('study_semester_id', $request->integer('study_semester_id'));
                });
            })
            ->when($request->filled('course_id'), function ($query) use ($request) {
                $query->where('course_id', $request->integer('course_id'));
            })
            ->when($request->filled('assistant_id'), function ($query) use ($request) {
                $query->where('assistant_id', $request->integer('assistant_id'));
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('is_active', $status === '1');
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('room', 'like', "%{$search}%")
                        ->orWhere('schedule', 'like', "%{$search}%")
                        ->orWhereHas('course', function ($courseQuery) use ($search) {
                            $courseQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('code', 'like', "%{$search}%");
                        })
                        ->orWhereHas('assistant', function ($assistantQuery) use ($search) {
                            $assistantQuery->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $this->studentAccess->attachResolvedStudentCounts($classes->getCollection());

        return view('admin.classes.index', [
            'classes' => $classes,
            'courses' => Course::with(['studySemester', 'academicYear'])->orderBy('name')->get(),
            'studySemesters' => StudySemester::orderBy('level')->get(),
            'assistants' => User::role('asisten')->active()->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.classes.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'assistant_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'asisten')->where('is_active', true);
                }),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('classes', 'name')->where(function ($query) use ($request) {
                    $query->where('course_id', $request->integer('course_id'));
                }),
            ],
            'room' => ['nullable', 'string', 'max:100'],
            'schedule' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => [
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'mahasiswa')->where('is_active', true);
                }),
            ],
        ]);

        $studentIds = $this->validStudentIdsForCourse(
            $validated['student_ids'] ?? [],
            (int) $validated['course_id']
        );

        unset($validated['student_ids']);

        $validated['is_active'] = $request->boolean('is_active');

        $class = PraktikumClass::create($validated);

        $this->syncStudents($class, $studentIds);

        return redirect()
            ->route('admin.kelas.show', $class)
            ->with('success', 'Kelas praktikum berhasil ditambahkan.');
    }

    public function show(PraktikumClass $praktikumClass): View
    {
        $praktikumClass->load([
            'course.academicYear',
            'course.studySemester.students',
            'assistant',
            'students.studySemester',
            'materials.creator',
            'assignments.creator',
            'attendances',
            'announcements',
        ]);

        $praktikumClass->loadCount([
            'students',
            'materials',
            'assignments',
            'attendances',
            'announcements',
        ]);

        $resolvedStudents = $this->studentAccess->studentsForClass($praktikumClass);

        return view('admin.classes.show', compact('praktikumClass', 'resolvedStudents'));
    }

    public function edit(PraktikumClass $praktikumClass): View
    {
        return view('admin.classes.edit', array_merge($this->formData(), [
            'praktikumClass' => $praktikumClass->load(['students', 'course.studySemester']),
        ]));
    }

    public function update(Request $request, PraktikumClass $praktikumClass): RedirectResponse
    {
        $validated = $request->validate([
            'course_id' => ['required', 'exists:courses,id'],
            'assistant_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'asisten')->where('is_active', true);
                }),
            ],
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('classes', 'name')
                    ->where(function ($query) use ($request) {
                        $query->where('course_id', $request->integer('course_id'));
                    })
                    ->ignore($praktikumClass->id),
            ],
            'room' => ['nullable', 'string', 'max:100'],
            'schedule' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => [
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'mahasiswa')->where('is_active', true);
                }),
            ],
        ]);

        $studentIds = $this->validStudentIdsForCourse(
            $validated['student_ids'] ?? [],
            (int) $validated['course_id']
        );

        unset($validated['student_ids']);

        $validated['is_active'] = $request->boolean('is_active');

        $praktikumClass->update($validated);

        $this->syncStudents($praktikumClass, $studentIds);

        return redirect()
            ->route('admin.kelas.show', $praktikumClass)
            ->with('success', 'Kelas praktikum berhasil diperbarui.');
    }

    public function destroy(PraktikumClass $praktikumClass): RedirectResponse
    {
        abort_if(
            $praktikumClass->materials()->exists()
            || $praktikumClass->assignments()->exists()
            || $praktikumClass->attendances()->exists()
            || $praktikumClass->announcements()->exists(),
            422,
            'Kelas tidak bisa dihapus karena masih memiliki materi, tugas, absensi, atau pengumuman.'
        );

        $praktikumClass->students()->detach();

        $praktikumClass->delete();

        return redirect()
            ->route('admin.kelas.index')
            ->with('success', 'Kelas praktikum berhasil dihapus.');
    }

    private function formData(): array
    {
        return [
            'courses' => Course::with(['academicYear', 'studySemester'])
                ->active()
                ->orderBy('study_semester_id')
                ->orderBy('name')
                ->get(),

            'assistants' => User::role('asisten')
                ->active()
                ->orderBy('name')
                ->get(),

            'students' => User::role('mahasiswa')
                ->with('studySemester')
                ->active()
                ->orderBy('study_semester_id')
                ->orderBy('name')
                ->get(),
        ];
    }

    private function validStudentIdsForCourse(array $studentIds, int $courseId): array
    {
        $course = Course::find($courseId);

        if (! $course) {
            return [];
        }

        return User::query()
            ->role('mahasiswa')
            ->active()
            ->where('study_semester_id', $course->study_semester_id)
            ->whereIn('id', $studentIds)
            ->pluck('id')
            ->all();
    }

    private function syncStudents(PraktikumClass $class, array $studentIds): void
    {
        $class->students()->sync($studentIds);

        // Akses utama mahasiswa dihitung dari study_semester_id.
        // class_students hanya dipakai untuk pembagian khusus/manual.
    }
}