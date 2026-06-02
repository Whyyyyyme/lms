<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\View\View;

class GradeController extends Controller
{
    use ResolvesClassAccess;

    public function index(): View
    {
        $classIds = $this->studentClassIds();

        $submissions = Submission::query()
            ->with(['assignment.kelas.course'])
            ->where('student_id', auth()->id())
            ->whereHas('assignment', fn ($query) => $query->whereIn('class_id', $classIds))
            ->latest('graded_at')
            ->paginate(10);

        $averageScore = Submission::query()
            ->where('student_id', auth()->id())
            ->whereHas('assignment', fn ($query) => $query->whereIn('class_id', $classIds))
            ->whereNotNull('score')
            ->avg('score');

        return view('student.grades.index', compact('submissions', 'averageScore'));
    }
}
