<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudySemester;
use App\Models\User;
use App\Notifications\StudentAccountActivated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;
use Throwable;

class UserController extends Controller
{
    private const MANAGED_ROLES = ['asisten', 'mahasiswa'];

    private const STUDENT_GROUPS = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];

    public function index(Request $request): View
    {
        $role = $request->input('role');
        $status = $request->input('status');

        $users = User::query()
            ->with(['studySemester', 'roles'])
            ->whereDoesntHave('roles', function ($query) {
                $query->where('name', 'admin');
            })
            ->where(function ($query) {
                $query->whereIn('role', self::MANAGED_ROLES)
                    ->orWhereHas('roles', function ($roleQuery) {
                        $roleQuery->whereIn('name', self::MANAGED_ROLES);
                    });
            })
            ->when(in_array($role, self::MANAGED_ROLES, true), function ($query) use ($role) {
                $query->where(function ($query) use ($role) {
                    $query->where('role', $role)
                        ->orWhereHas('roles', function ($roleQuery) use ($role) {
                            $roleQuery->where('name', $role);
                        });
                });
            })
            ->when(in_array($status, ['active', 'pending'], true), function ($query) use ($status) {
                $query->where('is_active', $status === 'active');
            })
            ->when($request->filled('study_semester_id'), function ($query) use ($request) {
                $query->where('study_semester_id', $request->integer('study_semester_id'));
            })
            ->when($request->filled('student_group'), function ($query) use ($request) {
                $query->where('student_group', strtoupper((string) $request->input('student_group')));
            })
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim((string) $request->input('search'));

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('nim_nip', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'studySemesters' => StudySemester::orderBy('level')->get(),
            'studentGroups' => self::STUDENT_GROUPS,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => collect(self::MANAGED_ROLES),
            'studySemesters' => StudySemester::active()->orderBy('level')->get(),
            'studentGroups' => self::STUDENT_GROUPS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'nim_nip' => ['nullable', 'string', 'max:50', 'unique:users,nim_nip'],

            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
                'not_regex:/@(lms\.test|example\.com|example\.test)$/i',
            ],

            'password' => ['required', 'string', 'min:8', 'confirmed'],

            'role' => ['required', Rule::in(self::MANAGED_ROLES)],

            'study_semester_id' => [
                'nullable',
                'required_if:role,mahasiswa',
                'exists:study_semesters,id',
            ],

            'student_group' => [
                'nullable',
                'required_if:role,mahasiswa',
                Rule::in(self::STUDENT_GROUPS),
            ],

            'is_active' => ['nullable', 'boolean'],
        ], [
            'email.not_regex' => 'Gunakan email aktif/asli, bukan email dummy seperti @lms.test atau @example.com.',
            'study_semester_id.required_if' => 'Semester mahasiswa wajib dipilih.',
            'student_group.required_if' => 'Kelas/Rombel mahasiswa wajib dipilih.',
            'student_group.in' => 'Kelas/Rombel mahasiswa tidak valid.',
        ]);

        $shouldNotifyStudent = false;

        $user = DB::transaction(function () use ($request, $validated, &$shouldNotifyStudent) {
            $role = $validated['role'];

            Role::findOrCreate($role, 'web');

            $data = Arr::except($validated, ['role', 'password']);
            $data['password'] = Hash::make($validated['password']);
            $data['is_active'] = $request->boolean('is_active');
            $data['email_verified_at'] = now();

            if ($role !== 'mahasiswa') {
                $data['study_semester_id'] = null;
                $data['student_group'] = null;
            } else {
                $data['student_group'] = strtoupper((string) $validated['student_group']);
            }

            if (Schema::hasColumn('users', 'role')) {
                $data['role'] = $role;
            }

            $user = User::create($data);

            $user->syncRoles([$role]);

            $this->syncStudentSemester(
                $user,
                $role,
                $validated['study_semester_id'] ?? null
            );

            $shouldNotifyStudent = $role === 'mahasiswa' && (bool) $user->is_active;

            return $user;
        });

        if ($shouldNotifyStudent) {
            $this->sendStudentActivatedNotification($user);
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function show(User $user): View
    {
        abort_if($user->hasRole('admin') || $user->role === 'admin', 403, 'Akun admin dikelola manual.');

        $user->load([
            'roles',
            'studySemester.courses.classes',
            'kelasDiikuti.course.studySemester',
            'kelasDiasisteni.course.studySemester',
        ]);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        abort_if($user->hasRole('admin') || $user->role === 'admin', 403, 'Akun admin dikelola manual.');

        $user->load(['roles', 'studySemester']);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => collect(self::MANAGED_ROLES),
            'studySemesters' => StudySemester::active()->orderBy('level')->get(),
            'studentGroups' => self::STUDENT_GROUPS,
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        abort_if($user->hasRole('admin') || $user->role === 'admin', 403, 'Akun admin dikelola manual.');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'nim_nip' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('users', 'nim_nip')->ignore($user->id),
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
                'not_regex:/@(lms\.test|example\.com|example\.test)$/i',
            ],

            'password' => ['nullable', 'string', 'min:8', 'confirmed'],

            'role' => ['required', Rule::in(self::MANAGED_ROLES)],

            'study_semester_id' => [
                'nullable',
                'required_if:role,mahasiswa',
                'exists:study_semesters,id',
            ],

            'student_group' => [
                'nullable',
                'required_if:role,mahasiswa',
                Rule::in(self::STUDENT_GROUPS),
            ],

            'is_active' => ['nullable', 'boolean'],
        ], [
            'email.not_regex' => 'Gunakan email aktif/asli, bukan email dummy seperti @lms.test atau @example.com.',
            'study_semester_id.required_if' => 'Semester mahasiswa wajib dipilih.',
            'student_group.required_if' => 'Kelas/Rombel mahasiswa wajib dipilih.',
            'student_group.in' => 'Kelas/Rombel mahasiswa tidak valid.',
        ]);

        $shouldNotifyStudent = false;

        DB::transaction(function () use ($request, $validated, $user, &$shouldNotifyStudent) {
            $role = $validated['role'];
            $wasInactive = ! (bool) $user->is_active;

            Role::findOrCreate($role, 'web');

            $data = Arr::except($validated, ['role', 'password']);
            $data['is_active'] = $request->boolean('is_active');

            if (! empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }

            if ($role !== 'mahasiswa') {
                $data['study_semester_id'] = null;
                $data['student_group'] = null;
            } else {
                $data['student_group'] = strtoupper((string) $validated['student_group']);
            }

            if (Schema::hasColumn('users', 'role')) {
                $data['role'] = $role;
            }

            $user->update($data);

            $user->syncRoles([$role]);

            $this->syncStudentSemester(
                $user,
                $role,
                $validated['study_semester_id'] ?? null
            );

            $shouldNotifyStudent = $role === 'mahasiswa'
                && $wasInactive
                && (bool) $user->is_active;
        });

        if ($shouldNotifyStudent) {
            $this->sendStudentActivatedNotification($user->fresh());
        }

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function verify(User $user): RedirectResponse
    {
        abort_if($user->hasRole('admin') || $user->role === 'admin', 403, 'Akun admin dikelola manual.');

        $isStudent = $user->role === 'mahasiswa' || $user->hasRole('mahasiswa');

        if (! $isStudent) {
            return redirect()
                ->route('admin.users.index')
                ->with('error', 'Hanya akun mahasiswa yang bisa diverifikasi.');
        }

        if ((bool) $user->is_active) {
            return redirect()
                ->route('admin.users.index')
                ->with('info', 'Akun mahasiswa ini sudah aktif.');
        }

        DB::transaction(function () use ($user) {
            Role::findOrCreate('mahasiswa', 'web');

            $data = [
                'is_active' => true,
            ];

            if (is_null($user->email_verified_at)) {
                $data['email_verified_at'] = now();
            }

            if (Schema::hasColumn('users', 'role')) {
                $data['role'] = 'mahasiswa';
            }

            $user->update($data);

            if (! $user->hasRole('mahasiswa')) {
                $user->syncRoles(['mahasiswa']);
            }
        });

        $this->sendStudentActivatedNotification($user->fresh());

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'Akun mahasiswa berhasil diverifikasi dan email aktivasi telah dikirim.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 422, 'Akun sendiri tidak boleh dihapus.');
        abort_if($user->hasRole('admin') || $user->role === 'admin', 403, 'Akun admin dikelola manual.');

        $user->delete();

        return redirect()
            ->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    private function syncStudentSemester(User $user, string $role, ?int $studySemesterId): void
    {
        if ($role !== 'mahasiswa' || ! $studySemesterId) {
            $user->semesterEnrollments()->update(['is_active' => false]);
            $user->kelasDiikuti()->detach();

            return;
        }

        $user->semesterEnrollments()->update(['is_active' => false]);

        $user->semesterEnrollments()->updateOrCreate(
            [
                'study_semester_id' => $studySemesterId,
                'academic_year_id' => null,
            ],
            [
                'is_active' => true,
                'enrolled_at' => now(),
            ]
        );

        // Mahasiswa tidak lagi dikunci hanya ke satu kelas utama.
        // Akses materi/tugas/absensi dihitung dari study_semester_id + student_group.
    }

    private function sendStudentActivatedNotification(?User $user): void
    {
        if (! $user) {
            return;
        }

        try {
            $user->notify(new StudentAccountActivated());
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}