<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class DashboardRedirectController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        $user = auth()->user();

        if ($user->hasRole('admin')) {
            return redirect()->route('admin.dashboard');
        }

        if ($user->hasRole('asisten')) {
            return redirect()->route('assistant.dashboard');
        }

        return redirect()->route('student.dashboard');
    }
}
