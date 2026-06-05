<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class GradeController extends Controller
{
    use ResolvesClassAccess;

    public function index(): View
    {
        $studentId = (int) auth()->id();
        $classIds = $this->studentClassIdArray();

        $submissions = $this->submissionQuery($studentId, $classIds)
            ->orderByRaw('graded_at IS NULL ASC')
            ->latest('graded_at')
            ->latest('submitted_at')
            ->paginate(10);

        return view('student.grades.index', [
            'submissions' => $submissions,
            'averageScore' => $this->averageScore($studentId, $classIds),
            'totalSubmissions' => $this->totalSubmissions($studentId, $classIds),
            'gradedSubmissions' => $this->gradedSubmissions($studentId, $classIds),
            'archivedClassesCount' => count($this->studentArchivedClassIdArray()),
            'isHistoryPage' => false,
        ]);
    }

    public function history(): View
    {
        $studentId = (int) auth()->id();
        $classIds = $this->studentArchivedClassIdArray();

        $submissions = $this->submissionQuery($studentId, $classIds)
            ->orderByRaw('graded_at IS NULL ASC')
            ->latest('graded_at')
            ->latest('submitted_at')
            ->paginate(10);

        return view('student.grades.history', [
            'submissions' => $submissions,
            'averageScore' => $this->averageScore($studentId, $classIds),
            'totalSubmissions' => $this->totalSubmissions($studentId, $classIds),
            'gradedSubmissions' => $this->gradedSubmissions($studentId, $classIds),
            'archivedClassesCount' => count($classIds),
            'isHistoryPage' => true,
        ]);
    }

    /** @param array<int> $classIds */
    private function submissionQuery(int $studentId, array $classIds): Builder
    {
        return Submission::query()
            ->with(['assignment.kelas.course.academicYear'])
            ->where('student_id', $studentId)
            ->whereHas('assignment', function ($query) use ($classIds) {
                $query->published()
                    ->whereIn('class_id', $classIds);
            });
    }

    /** @param array<int> $classIds */
    private function averageScore(int $studentId, array $classIds): ?float
    {
        $average = $this->submissionQuery($studentId, $classIds)
            ->whereNotNull('score')
            ->avg('score');

        return $average !== null ? (float) $average : null;
    }

    /** @param array<int> $classIds */
    private function totalSubmissions(int $studentId, array $classIds): int
    {
        return $this->submissionQuery($studentId, $classIds)->count();
    }

    /** @param array<int> $classIds */
    private function gradedSubmissions(int $studentId, array $classIds): int
    {
        return $this->submissionQuery($studentId, $classIds)
            ->whereNotNull('score')
            ->count();
    }

    /** @return array<int> */
    private function studentClassIdArray(): array
    {
        return collect($this->studentClassIds())
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /** @return array<int> */
    private function studentArchivedClassIdArray(): array
    {
        return collect($this->studentArchivedClassIds())
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }
}
