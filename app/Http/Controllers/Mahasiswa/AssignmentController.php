<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    use ResolvesClassAccess;

    public function index(): View
    {
        $studentClassIds = $this->studentClassIdArray();

        $assignments = Assignment::query()
            ->with([
                'kelas.course',
                'submissions' => fn ($query) => $query->where('student_id', auth()->id()),
            ])
            ->whereIn('class_id', $studentClassIds)
            ->published()
            ->orderBy('deadline')
            ->paginate(10);

        return view('student.assignments.index', compact('assignments'));
    }

    public function show(Assignment $assignment): View
    {
        $this->ensureStudentCanAccessAssignment($assignment);

        $assignment->load([
            'kelas.course',
            'creator',
            'submissions' => fn ($query) => $query->where('student_id', auth()->id()),
        ]);

        $submission = $assignment->submissions->first();

        return view('student.assignments.show', compact('assignment', 'submission'));
    }

    public function submit(Request $request, Assignment $assignment): RedirectResponse
    {
        $this->ensureStudentCanAccessAssignment($assignment);

        abort_if(
            now()->greaterThan($assignment->deadline),
            422,
            'Deadline tugas sudah berakhir.'
        );

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:102400'],
        ], [
            'file.required' => 'File submission wajib diunggah.',
            'file.uploaded' => 'File gagal diunggah. Biasanya karena ukuran file melebihi upload_max_filesize/post_max_size di php.ini, atau file terlalu besar.',
            'file.max' => 'Ukuran file maksimal 100 MB.',
            'file.file' => 'Upload harus berupa file yang valid.',
        ]);

        $oldSubmission = Submission::where('assignment_id', $assignment->id)
            ->where('student_id', auth()->id())
            ->first();

        if ($oldSubmission?->file_path) {
            Storage::disk('public')->delete($oldSubmission->file_path);
        }

        $filePath = $validated['file']->store('submissions', 'public');

        Submission::updateOrCreate(
            [
                'assignment_id' => $assignment->id,
                'student_id' => auth()->id(),
            ],
            [
                'file_path' => $filePath,
                'submitted_at' => now(),
                'score' => null,
                'feedback' => null,
                'graded_at' => null,
            ]
        );

        return back()->with('success', 'Tugas berhasil dikumpulkan.');
    }

    public function updateSubmission(Request $request, Submission $submission): RedirectResponse
    {
        abort_unless((int) $submission->student_id === (int) auth()->id(), 403);

        $submission->load('assignment');

        $this->ensureStudentCanAccessAssignment($submission->assignment);

        abort_if(
            now()->greaterThan($submission->assignment->deadline),
            422,
            'Deadline tugas sudah berakhir.'
        );

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:102400'],
        ], [
            'file.required' => 'File submission wajib diunggah.',
            'file.uploaded' => 'File gagal diunggah. Biasanya karena ukuran file melebihi upload_max_filesize/post_max_size di php.ini, atau file terlalu besar.',
            'file.max' => 'Ukuran file maksimal 100 MB.',
            'file.file' => 'Upload harus berupa file yang valid.',
        ]);

        if ($submission->file_path) {
            Storage::disk('public')->delete($submission->file_path);
        }

        $submission->update([
            'file_path' => $validated['file']->store('submissions', 'public'),
            'submitted_at' => now(),
            'score' => null,
            'feedback' => null,
            'graded_at' => null,
        ]);

        return back()->with('success', 'Submission tugas berhasil diperbarui.');
    }

    private function ensureStudentCanAccessAssignment(Assignment $assignment): void
    {
        $studentClassIds = $this->studentClassIdArray();

        abort_unless(
            in_array((int) $assignment->class_id, $studentClassIds, true),
            403
        );

        abort_unless(
            $assignment->is_published,
            404
        );
    }

    private function studentClassIdArray(): array
    {
        return collect($this->studentClassIds())
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }
}