<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\StudySemester;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    private const MANAGED_ROLES = ['asisten', 'mahasiswa'];
    public function index(Request $request): View
{
    $role = $request->input('role');

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
        ->when($request->filled('study_semester_id'), function ($query) use ($request) {
            $query->where('study_semester_id', $request->integer('study_semester_id'));
        })
        ->when($request->filled('search'), function ($query) use ($request) {
            $search = $request->string('search');

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
    ]);
}

    public function create(): View
    {
        return view('admin.users.create', [
            'roles' => collect(self::MANAGED_ROLES),
            'studySemesters' => StudySemester::active()->orderBy('level')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim_nip' => ['nullable', 'string', 'max:50', 'unique:users,nim_nip'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(self::MANAGED_ROLES)],
            'study_semester_id' => [
                'nullable',
                'required_if:role,mahasiswa',
                'exists:study_semesters,id',
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data = Arr::except($validated, ['role', 'password']);
        $data['password'] = Hash::make($validated['password']);
        $data['is_active'] = $request->boolean('is_active');
        $data['email_verified_at'] = now();

        if ($validated['role'] !== 'mahasiswa') {
            $data['study_semester_id'] = null;
        }

        if (Schema::hasColumn('users', 'role')) {
            $data['role'] = $validated['role'];
        }

        $user = User::create($data);
        $user->syncRoles([$validated['role']]);
        $this->syncStudentSemester($user, $validated['role'], $validated['study_semester_id'] ?? null);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
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
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {   
        abort_if($user->hasRole('admin') || $user->role === 'admin', 403, 'Akun admin dikelola manual.');
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim_nip' => ['nullable', 'string', 'max:50', Rule::unique('users', 'nim_nip')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(self::MANAGED_ROLES)],
            'study_semester_id' => [
                'nullable',
                'required_if:role,mahasiswa',
                'exists:study_semesters,id',
            ],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data = Arr::except($validated, ['role', 'password']);
        $data['is_active'] = $request->boolean('is_active');

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        if ($validated['role'] !== 'mahasiswa') {
            $data['study_semester_id'] = null;
        }

        if (Schema::hasColumn('users', 'role')) {
            $data['role'] = $validated['role'];
        }

        $user->update($data);
        $user->syncRoles([$validated['role']]);
        $this->syncStudentSemester($user, $validated['role'], $validated['study_semester_id'] ?? null);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if($user->is(auth()->user()), 422, 'Akun sendiri tidak boleh dihapus.');
        abort_if($user->hasRole('admin') || $user->role === 'admin', 403, 'Akun admin dikelola manual.');

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
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
        // Akses materi/tugas akan dihitung dari semester mahasiswa + kelas yang memang tersedia di semester tersebut.
    }
}
