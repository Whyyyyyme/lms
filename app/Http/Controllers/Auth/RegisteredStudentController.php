<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\StudySemester;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredStudentController extends Controller
{
    public function create(): View
    {
        return view('auth.register', [
            'studySemesters' => StudySemester::active()
                ->orderBy('level')
                ->get(),
            'studentGroups' => ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'nim_nip' => ['required', 'string', 'max:50', 'unique:users,nim_nip'],
            'email' => [
                'required',
                'email',
                'max:255',
                'unique:users,email',
                'not_regex:/@(lms\.test|example\.com|example\.test)$/i',
            ],
            'study_semester_id' => [
                'required',
                'exists:study_semesters,id',
            ],
            'student_group' => [
                'required',
                'string',
                'in:A,B,C,D,E,F,G,H',
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'study_semester_id' => ['required', Rule::exists('study_semesters', 'id')->where('is_active', true)],
        ], [
            'name.required' => 'Nama lengkap wajib diisi.',
            'nim_nip.required' => 'NIM wajib diisi.',
            'nim_nip.unique' => 'NIM sudah terdaftar.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak sama.',
            'study_semester_id.required' => 'Semester mahasiswa wajib dipilih.',
            'student_group.required' => 'Kelas/Rombel mahasiswa wajib dipilih.',
            'student_group.in' => 'Kelas/Rombel mahasiswa tidak valid.',
            'password.required' => 'Password LMS wajib diisi.',
            'password.min' => 'Password LMS minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password LMS tidak cocok.',
        ]);

        $student = User::create([
            'name' => $validated['name'],
            'nim_nip' => $validated['nim_nip'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'mahasiswa',
            'study_semester_id' => $validated['study_semester_id'],
            'student_group' => $validated['student_group'],
            'is_active' => false,
        ]);

        $student->syncRoles(['mahasiswa']);

        $student->semesterEnrollments()->create([
            'study_semester_id' => $validated['study_semester_id'],
            'academic_year_id' => null,
            'is_active' => true,
            'enrolled_at' => now(),
        ]);

        return redirect()
            ->route('login')
            ->with('status', 'Pendaftaran berhasil. Akun kamu masih menunggu verifikasi admin sebelum bisa login.');
    }
}
