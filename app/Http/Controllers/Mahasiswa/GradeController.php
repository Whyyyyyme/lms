<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\View\View;

class GradeController extends Controller
{
    public function index(): View
    {
        $submissions = Submission::query()
            ->with(['assignment.kelas.course'])
            ->where('student_id', auth()->id())
            ->latest('graded_at')
            ->paginate(10);

        $averageScore = Submission::where('student_id', auth()->id())
            ->whereNotNull('score')
            ->avg('score');

        return view('student.grades.index', compact('submissions', 'averageScore'));
    }
}
