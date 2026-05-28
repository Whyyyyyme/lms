<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\Material;
use App\Models\PraktikumClass;
use App\Models\Submission;
use App\Models\User;
use Illuminate\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        $statistics = [
            'total_user' => User::count(),
            'total_admin' => User::role('admin')->count(),
            'total_asisten' => User::role('asisten')->count(),
            'total_mahasiswa' => User::role('mahasiswa')->count(),
            'total_matakuliah' => Course::count(),
            'total_kelas' => PraktikumClass::count(),
            'total_materi' => Material::count(),
            'total_tugas' => Assignment::count(),
            'total_submission' => Submission::count(),
            'total_sesi_absensi' => Attendance::count(),
        ];

        $latestUsers = User::latest()->limit(5)->get();
        $latestSubmissions = Submission::with(['student', 'assignment.kelas.course'])
            ->latest('submitted_at')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact('statistics', 'latestUsers', 'latestSubmissions'));
    }
}
