<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Concerns\HandlesLmsNotifications;
use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SubmissionController extends Controller
{
    use HandlesLmsNotifications, ResolvesClassAccess;

    public function index(Request $request): View
    {
        $classIds = $this->assistantClassesQuery()->pluck('id');

        $submissions = Submission::query()
            ->with(['student', 'assignment.kelas.course'])
            ->whereHas('assignment', fn ($query) => $query->whereIn('class_id', $classIds))
            ->when($request->filled('status'), function ($query) use ($request) {
                if ($request->status === 'sudah_dinilai') {
                    $query->whereNotNull('graded_at');
                }

                if ($request->status === 'belum_dinilai') {
                    $query->whereNull('graded_at');
                }
            })
            ->latest('submitted_at')
            ->paginate(15)
            ->withQueryString();

        return view('assistant.submissions.index', compact('submissions'));
    }

    public function show(Submission $submission): View
    {
        $submission->load(['student', 'assignment.kelas.course']);
        $this->assistantClassOrFail((int) $submission->assignment->class_id);

        return view('assistant.submissions.show', compact('submission'));
    }

    public function grade(Request $request, Submission $submission): RedirectResponse
    {
        $submission->load(['student', 'assignment.kelas']);
        $this->assistantClassOrFail((int) $submission->assignment->class_id);

        $validated = $request->validate([
            'score' => ['required', 'numeric', 'min:0', 'max:' . $submission->assignment->max_score],
            'feedback' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['draft', 'final'])],
        ]);

        $submission->update([
            'score' => $validated['score'],
            'feedback' => $validated['feedback'] ?? null,
            'graded_at' => now(),
        ]);

        $this->notifyUsers(
            collect([$submission->student]),
            'grade_submitted',
            'Nilai Tugas Sudah Diinput',
            "Nilai untuk tugas {$submission->assignment->title} sudah tersedia.",
            ['submission_id' => $submission->id, 'assignment_id' => $submission->assignment_id]
        );

        return back()->with('success', 'Nilai dan feedback berhasil disimpan.');
    }
}
