<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Assignment;
use App\Models\Attendance;
use App\Models\Material;
use App\Models\Submission;
use Illuminate\View\View;

class MahasiswaDashboardController extends Controller
{
    use ResolvesClassAccess;

    public function index(): View
    {
        $classIds = $this->studentClassIds();

        $statistics = [
            'total_materi' => Material::published()->whereIn('class_id', $classIds)->count(),
            'total_tugas' => Assignment::whereIn('class_id', $classIds)->count(),
            'tugas_belum_dikumpulkan' => Assignment::whereIn('class_id', $classIds)
                ->whereDoesntHave('submissions', fn ($query) => $query->where('student_id', auth()->id()))
                ->count(),
            'total_nilai' => Submission::where('student_id', auth()->id())->whereNotNull('graded_at')->count(),
            'absensi_terbuka' => Attendance::whereIn('class_id', $classIds)->where('is_open', true)->count(),
        ];

        $latestMaterials = Material::with('kelas.course')
            ->published()
            ->whereIn('class_id', $classIds)
            ->latest('published_at')
            ->limit(5)
            ->get();

        $upcomingAssignments = Assignment::with(['kelas.course', 'submissions' => fn ($query) => $query->where('student_id', auth()->id())])
            ->whereIn('class_id', $classIds)
            ->where('deadline', '>=', now())
            ->orderBy('deadline')
            ->limit(5)
            ->get();

        $announcements = Announcement::with('kelas.course')
            ->whereIn('class_id', $classIds)
            ->latest()
            ->limit(5)
            ->get();

        return view('student.dashboard', compact('statistics', 'latestMaterials', 'upcomingAssignments', 'announcements'));
    }
}
