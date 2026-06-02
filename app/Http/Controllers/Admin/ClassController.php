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
    private const CLASS_TYPES = [
        'regular' => 'Reguler',
        'combined' => 'Gabungan',
    ];

    private const STUDENT_GROUPS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

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
            ->when($request->filled('class_type'), function ($query) use ($request) {
                $query->where('class_type', $request->input('class_type'));
            })
            ->when($request->filled('student_group'), function ($query) use ($request) {
                $group = strtoupper((string) $request->input('student_group'));

                $query->where(function ($query) use ($group) {
                    $query->where('student_group', $group)
                        ->orWhereJsonContains('group_members', $group);
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('is_active', $status === '1');
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('room', 'like', "%{$search}%")
                        ->orWhere('schedule', 'like', "%{$search}%")
                        ->orWhere('group_label', 'like', "%{$search}%")
                        ->orWhere('student_group', 'like', "%{$search}%")
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

        $this->appendIndexComputedAttributes($classes);

        return view('admin.classes.index', [
            'classes' => $classes,
            'courses' => Course::with(['studySemester', 'academicYear'])->orderBy('name')->get(),
            'studySemesters' => StudySemester::orderBy('level')->get(),
            'assistants' => User::role('asisten')->active()->orderBy('name')->get(),
            'classTypes' => self::CLASS_TYPES,
            'studentGroups' => self::STUDENT_GROUPS,
        ]);
    }

    public function create(): View
    {
        return view('admin.classes.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateClassRequest($request);

        $studentIds = $this->validStudentIdsForCourse(
            $validated['student_ids'] ?? [],
            (int) $validated['course_id']
        );

        unset($validated['student_ids']);

        $validated = $this->normalizeClassPayload($validated, $request);

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

        $automaticStudents = $this->automaticStudentsForClass($praktikumClass);

        return view('admin.classes.show', compact('praktikumClass', 'automaticStudents'));
    }

    public function edit(PraktikumClass $praktikumClass): View
    {
        return view('admin.classes.edit', array_merge($this->formData(), [
            'praktikumClass' => $praktikumClass->load(['students', 'course.studySemester']),
        ]));
    }

    public function update(Request $request, PraktikumClass $praktikumClass): RedirectResponse
    {
        $validated = $this->validateClassRequest($request, $praktikumClass);

        $studentIds = $this->validStudentIdsForCourse(
            $validated['student_ids'] ?? [],
            (int) $validated['course_id']
        );

        unset($validated['student_ids']);

        $validated = $this->normalizeClassPayload($validated, $request);

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
                ->orderBy('student_group')
                ->orderBy('name')
                ->get(),

            'classTypes' => self::CLASS_TYPES,
            'studentGroups' => self::STUDENT_GROUPS,
        ];
    }

    private function validateClassRequest(Request $request, ?PraktikumClass $praktikumClass = null): array
    {
        return $request->validate([
            'course_id' => ['required', 'exists:courses,id'],

            'assistant_id' => [
                'nullable',
                Rule::exists('users', 'id')->where(function ($query) {
                    $query->where('role', 'asisten')->where('is_active', true);
                }),
            ],

            'class_type' => [
                'required',
                Rule::in(array_keys(self::CLASS_TYPES)),
            ],

            'student_group' => [
                'nullable',
                'required_if:class_type,regular',
                Rule::in(self::STUDENT_GROUPS),
            ],

            'group_label' => [
                'nullable',
                'required_if:class_type,combined',
                'string',
                'max:50',
            ],

            'group_members' => [
                'nullable',
                'required_if:class_type,combined',
                'array',
                'min:1',
            ],

            'group_members.*' => [
                Rule::in(self::STUDENT_GROUPS),
            ],

            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('classes', 'name')
                    ->where(function ($query) use ($request) {
                        $query->where('course_id', $request->integer('course_id'));
                    })
                    ->ignore($praktikumClass?->id),
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
        ], [
            'course_id.required' => 'Mata kuliah wajib dipilih.',
            'course_id.exists' => 'Mata kuliah tidak valid.',

            'assistant_id.exists' => 'Asisten tidak valid atau belum aktif.',

            'class_type.required' => 'Tipe kelas wajib dipilih.',
            'class_type.in' => 'Tipe kelas tidak valid.',

            'student_group.required_if' => 'Rombel wajib dipilih untuk kelas reguler.',
            'student_group.in' => 'Rombel kelas tidak valid.',

            'group_label.required_if' => 'Label gabungan wajib diisi untuk kelas gabungan.',
            'group_label.max' => 'Label gabungan maksimal 50 karakter.',

            'group_members.required_if' => 'Rombel gabungan wajib dipilih.',
            'group_members.array' => 'Rombel gabungan tidak valid.',
            'group_members.min' => 'Minimal pilih satu rombel untuk kelas gabungan.',
            'group_members.*.in' => 'Salah satu rombel gabungan tidak valid.',

            'name.required' => 'Nama kelas wajib diisi.',
            'name.unique' => 'Nama kelas sudah digunakan pada mata kuliah ini.',

            'room.max' => 'Ruangan maksimal 100 karakter.',
            'schedule.max' => 'Jadwal maksimal 255 karakter.',
        ]);
    }

    private function normalizeClassPayload(array $validated, Request $request): array
    {
        $validated['is_active'] = $request->boolean('is_active');
        $validated['class_type'] = $validated['class_type'] ?? 'regular';

        if ($validated['class_type'] === 'regular') {
            $validated['student_group'] = strtoupper((string) ($validated['student_group'] ?? ''));
            $validated['group_label'] = null;
            $validated['group_members'] = null;

            return $validated;
        }

        $groupMembers = collect($validated['group_members'] ?? [])
            ->map(fn ($group) => strtoupper((string) $group))
            ->filter(fn ($group) => in_array($group, self::STUDENT_GROUPS, true))
            ->unique()
            ->values()
            ->all();

        $validated['student_group'] = null;
        $validated['group_label'] = strtoupper(trim((string) ($validated['group_label'] ?? '')));
        $validated['group_members'] = $groupMembers;

        return $validated;
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

    private function automaticStudentsForClass(PraktikumClass $class)
    {
        $class->loadMissing('course.studySemester');

        if (! $class->course?->study_semester_id) {
            return collect();
        }

        $query = User::query()
            ->role('mahasiswa')
            ->active()
            ->with('studySemester')
            ->where('study_semester_id', $class->course->study_semester_id);

        if (($class->class_type ?? 'regular') === 'regular') {
            if (! $class->student_group) {
                return collect();
            }

            $query->where('student_group', $class->student_group);
        }

        if (($class->class_type ?? 'regular') === 'combined') {
            $members = $this->normalizedGroupMembers($class);

            if (empty($members)) {
                return collect();
            }

            $query->whereIn('student_group', $members);
        }

        return $query
            ->orderBy('student_group')
            ->orderBy('name')
            ->get();
    }

    private function automaticStudentsCountForClass(PraktikumClass $class): int
    {
        $class->loadMissing('course');

        if (! $class->course?->study_semester_id) {
            return 0;
        }

        $query = User::query()
            ->role('mahasiswa')
            ->active()
            ->where('study_semester_id', $class->course->study_semester_id);

        if (($class->class_type ?? 'regular') === 'regular') {
            if (! $class->student_group) {
                return 0;
            }

            return (clone $query)
                ->where('student_group', $class->student_group)
                ->count();
        }

        if (($class->class_type ?? 'regular') === 'combined') {
            $members = $this->normalizedGroupMembers($class);

            if (empty($members)) {
                return 0;
            }

            return (clone $query)
                ->whereIn('student_group', $members)
                ->count();
        }

        return 0;
    }

    private function normalizedGroupMembers(PraktikumClass $class): array
    {
        return collect($class->group_members ?? [])
            ->map(fn ($group) => strtoupper((string) $group))
            ->filter(fn ($group) => in_array($group, self::STUDENT_GROUPS, true))
            ->unique()
            ->values()
            ->all();
    }

    private function groupDisplayForClass(PraktikumClass $class): string
    {
        $classType = $class->class_type ?? 'regular';

        if ($classType === 'regular') {
            return $class->student_group
                ? 'Kelas ' . $class->student_group
                : 'Belum diatur';
        }

        if ($classType === 'combined') {
            $members = $this->normalizedGroupMembers($class);

            if (empty($members)) {
                return 'Belum diatur';
            }

            return collect($members)
                ->map(fn ($group) => 'Kelas ' . $group)
                ->implode(', ');
        }

        return 'Belum diatur';
    }

    private function appendIndexComputedAttributes($classes): void
    {
        $classes->getCollection()->transform(function (PraktikumClass $class) {
            $class->automatic_students_count = $this->automaticStudentsCountForClass($class);
            $class->class_type_label = self::CLASS_TYPES[$class->class_type ?? 'regular'] ?? 'Reguler';
            $class->group_display = $this->groupDisplayForClass($class);

            return $class;
        });
    }

    private function syncStudents(PraktikumClass $class, array $studentIds): void
    {
        $class->students()->sync($studentIds);

        // Akses utama mahasiswa dihitung dari study_semester_id + student_group.
        // class_students tetap dipakai hanya untuk mahasiswa manual/khusus tambahan.
    }
}