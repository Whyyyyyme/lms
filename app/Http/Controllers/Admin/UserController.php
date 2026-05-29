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
    public function index(Request $request): View
    {
        $users = User::query()
            ->with(['studySemester', 'roles'])
            ->when($request->filled('role'), fn ($query) => $query->role($request->role))
            ->when($request->filled('study_semester_id'), fn ($query) => $query->where('study_semester_id', $request->integer('study_semester_id')))
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
            'roles' => Role::query()->orderBy('name')->pluck('name'),
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
            'role' => ['required', Rule::in(['admin', 'asisten', 'mahasiswa'])],
            'study_semester_id' => ['nullable', 'exists:study_semesters,id'],
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
        $user->load(['studySemester.courses', 'kelasDiikuti.course.studySemester', 'kelasDiasisteni.course.studySemester', 'roles']);

        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $user->load(['roles', 'studySemester']);

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => Role::query()->orderBy('name')->pluck('name'),
            'studySemesters' => StudySemester::active()->orderBy('level')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim_nip' => ['nullable', 'string', 'max:50', Rule::unique('users', 'nim_nip')->ignore($user->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['admin', 'asisten', 'mahasiswa'])],
            'study_semester_id' => ['nullable', 'exists:study_semesters,id'],
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
