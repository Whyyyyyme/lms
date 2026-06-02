<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Email atau password salah.',
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user();

        if (! $user->is_active) {
            $this->logoutCurrentSession($request);

            throw ValidationException::withMessages([
                'email' => 'Akun kamu belum aktif. Silakan hubungi admin.',
            ]);
        }

        $dashboardRoute = $this->dashboardRouteFor($user);

        if (! $dashboardRoute) {
            $this->logoutCurrentSession($request);

            throw ValidationException::withMessages([
                'email' => 'Role akun belum valid. Silakan hubungi admin.',
            ]);
        }

        return redirect()->route($dashboardRoute);
    }

    public function destroy(Request $request): RedirectResponse
    {
        $this->logoutCurrentSession($request);

        return redirect()->route('login')
            ->with('status', 'Kamu berhasil logout.');
    }

    private function dashboardRouteFor(User $user): ?string
    {
        if ($user->hasRole('admin') || $user->role === 'admin') {
            return 'admin.dashboard';
        }

        if ($user->hasRole('asisten') || $user->role === 'asisten') {
            return 'assistant.dashboard';
        }

        if ($user->hasRole('mahasiswa') || $user->role === 'mahasiswa') {
            return 'student.dashboard';
        }

        return null;
    }

    private function logoutCurrentSession(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}