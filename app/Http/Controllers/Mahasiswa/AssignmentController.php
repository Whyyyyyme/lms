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
use Throwable;

class AssignmentController extends Controller
{
    use ResolvesClassAccess;

    /**
     * Format file yang boleh dikumpulkan mahasiswa.
     *
     * Catatan:
     * - Format executable/script sengaja tidak diizinkan.
     * - Sesuaikan daftar ini jika dosen/asisten membutuhkan format lain.
     */
    private const ALLOWED_SUBMISSION_FILE_MIMES = 'pdf,doc,docx,ppt,pptx,xls,xlsx,zip,rar,txt,csv,jpg,jpeg,png';

    private const MAX_SUBMISSION_FILE_SIZE_KB = 102400;

    public function index(): View
    {
        $studentClassIds = $this->studentClassIdArray();

        $assignments = Assignment::query()
            ->with([
                'kelas.course.academicYear',
                'submissions' => fn ($query) => $query->where('student_id', auth()->id()),
            ])
            ->whereIn('class_id', $studentClassIds)
            ->published()
            ->orderBy('deadline')
            ->paginate(10);

        return view('student.assignments.index', [
            'assignments' => $assignments,
            'archivedClassesCount' => count($this->studentArchivedClassIdArray()),
        ]);
    }

    public function history(): View
    {
        $studentClassIds = $this->studentArchivedClassIdArray();

        $assignments = Assignment::query()
            ->with([
                'kelas.course.academicYear',
                'submissions' => fn ($query) => $query->where('student_id', auth()->id()),
            ])
            ->whereIn('class_id', $studentClassIds)
            ->published()
            ->latest('deadline')
            ->paginate(10);

        return view('student.assignments.history', compact('assignments'));
    }

    public function show(Assignment $assignment): View
    {
        $this->ensureStudentCanViewAssignment($assignment);

        $assignment->load([
            'kelas.course.academicYear',
            'creator',
            'submissions' => fn ($query) => $query->where('student_id', auth()->id()),
        ]);

        $submission = $assignment->submissions->first();
        $isArchivedAssignment = in_array((int) $assignment->class_id, $this->studentArchivedClassIdArray(), true)
            && ! in_array((int) $assignment->class_id, $this->studentClassIdArray(), true);

        return view('student.assignments.show', compact('assignment', 'submission', 'isArchivedAssignment'));
    }

    public function submit(Request $request, Assignment $assignment): RedirectResponse
    {
        $this->ensureStudentCanSubmitAssignment($assignment);

        abort_if(
            now()->greaterThan($assignment->deadline),
            422,
            'Deadline tugas sudah berakhir.'
        );

        $validated = $request->validate($this->submissionValidationRules(), $this->submissionValidationMessages());

        $oldSubmission = Submission::where('assignment_id', $assignment->id)
            ->where('student_id', auth()->id())
            ->first();

        $oldFilePath = $oldSubmission?->file_path;
        $newFilePath = $validated['file']->store('submissions', 'public');

        try {
            Submission::updateOrCreate(
                [
                    'assignment_id' => $assignment->id,
                    'student_id' => auth()->id(),
                ],
                [
                    'file_path' => $newFilePath,
                    'submitted_at' => now(),
                    'score' => null,
                    'feedback' => null,
                    'graded_at' => null,
                ]
            );
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newFilePath);

            throw $exception;
        }

        $this->deleteOldSubmissionFile($oldFilePath, $newFilePath);

        return back()->with('success', 'Tugas berhasil dikumpulkan.');
    }

    public function updateSubmission(Request $request, Submission $submission): RedirectResponse
    {
        abort_unless((int) $submission->student_id === (int) auth()->id(), 403);

        $submission->load('assignment');

        $this->ensureStudentCanSubmitAssignment($submission->assignment);

        abort_if(
            now()->greaterThan($submission->assignment->deadline),
            422,
            'Deadline tugas sudah berakhir.'
        );

        $validated = $request->validate($this->submissionValidationRules(), $this->submissionValidationMessages());

        $oldFilePath = $submission->file_path;
        $newFilePath = $validated['file']->store('submissions', 'public');

        try {
            $submission->update([
                'file_path' => $newFilePath,
                'submitted_at' => now(),
                'score' => null,
                'feedback' => null,
                'graded_at' => null,
            ]);
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($newFilePath);

            throw $exception;
        }

        $this->deleteOldSubmissionFile($oldFilePath, $newFilePath);

        return back()->with('success', 'Submission tugas berhasil diperbarui.');
    }

    private function ensureStudentCanViewAssignment(Assignment $assignment): void
    {
        abort_unless(
            in_array((int) $assignment->class_id, $this->studentAllClassIdArray(), true),
            403
        );

        abort_unless(
            $assignment->is_published,
            404
        );
    }

    private function ensureStudentCanSubmitAssignment(Assignment $assignment): void
    {
        abort_unless(
            in_array((int) $assignment->class_id, $this->studentClassIdArray(), true),
            403,
            'Tugas ini berasal dari tahun akademik yang sudah selesai sehingga hanya bisa dilihat sebagai riwayat.'
        );

        abort_unless(
            $assignment->is_published,
            404
        );
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

    /** @return array<int> */
    private function studentAllClassIdArray(): array
    {
        return collect($this->studentAllClassIds())
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    private function submissionValidationRules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:' . self::ALLOWED_SUBMISSION_FILE_MIMES,
                'max:' . self::MAX_SUBMISSION_FILE_SIZE_KB,
            ],
        ];
    }

    private function submissionValidationMessages(): array
    {
        return [
            'file.required' => 'File submission wajib diunggah.',
            'file.uploaded' => 'File gagal diunggah. Biasanya karena ukuran file melebihi upload_max_filesize/post_max_size di php.ini, atau file terlalu besar.',
            'file.max' => 'Ukuran file maksimal 100 MB.',
            'file.file' => 'Upload harus berupa file yang valid.',
            'file.mimes' => 'Format file submission harus PDF, DOC, DOCX, PPT, PPTX, XLS, XLSX, ZIP, RAR, TXT, CSV, JPG, JPEG, atau PNG.',
        ];
    }

    private function deleteOldSubmissionFile(?string $oldFilePath, ?string $newFilePath): void
    {
        if (! $oldFilePath || $oldFilePath === $newFilePath) {
            return;
        }

        Storage::disk('public')->delete($oldFilePath);
    }
}
