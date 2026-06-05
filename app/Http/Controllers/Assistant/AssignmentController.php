<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Concerns\HandlesLmsNotifications;
use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Services\Ai\FileTextExtractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class AssignmentController extends Controller
{
    use HandlesLmsNotifications, ResolvesClassAccess;

    private const ALLOWED_ASSIGNMENT_FILE_MIMES = 'pdf,docx,txt,md,csv';

    private const MAX_ASSIGNMENT_FILE_SIZE_KB = 102400;

    public function index(Request $request): View
    {
        $selectedClass = $this->selectedAssistantClassFromRequest($request);

        $assignments = Assignment::query()
            ->with(['kelas.course', 'creator'])
            ->withCount('submissions')
            ->whereIn('class_id', $this->assistantClassesQuery()->pluck('id'))
            ->when($selectedClass, fn ($query) => $query->where('class_id', $selectedClass->id))
            ->latest('deadline')
            ->paginate(10);

        return view('assistant.assignments.index', compact('assignments', 'selectedClass'));
    }

    public function create(Request $request): View
    {
        return view('assistant.assignments.create', [
            'classes' => $this->assistantClassesQuery()->with(['course.studySemester'])->get(),
            'selectedClass' => $this->selectedAssistantClassFromRequest($request),
        ]);
    }

    public function store(Request $request, FileTextExtractor $fileTextExtractor): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'file' => [
                'nullable',
                'file',
                'mimes:' . self::ALLOWED_ASSIGNMENT_FILE_MIMES,
                'max:' . self::MAX_ASSIGNMENT_FILE_SIZE_KB,
            ],

            'deadline' => ['required', 'date', 'after:now'],
            'max_score' => ['required', 'integer', 'min:1', 'max:1000'],
            'published_at' => ['nullable', 'date', 'before_or_equal:deadline'],
        ], $this->validationMessages());

        $class = $this->assistantClassOrFail((int) $validated['class_id']);

        $filePath = null;
        $extractedText = null;

        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('assignments', 'public');
            $extractedText = $fileTextExtractor->extractFromStoragePath($filePath, 'public');
        }

        $publishedAt = $request->filled('published_at')
            ? Carbon::parse($validated['published_at'], config('app.timezone'))
            : null;

        $assignment = Assignment::create([
            'class_id' => $class->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path' => $filePath,
            'extracted_text' => $extractedText,
            'deadline' => Carbon::parse($validated['deadline'], config('app.timezone')),
            'max_score' => $validated['max_score'],
            'created_by' => auth()->id(),
            'published_at' => $publishedAt,
            'published_notification_sent_at' => null,
        ]);

        if ($this->assignmentShouldAppearNow($assignment)) {
            $this->sendAssignmentCreatedNotification($assignment);

            $assignment->update([
                'published_notification_sent_at' => now(),
            ]);
        }

        return redirect()
            ->route('assistant.courses.show', $class)
            ->with('success', $this->assignmentSuccessMessage('Tugas berhasil dibuat.', $filePath, $extractedText));
    }

    public function show(Assignment $assignment): View
    {
        $this->assistantClassOrFail((int) $assignment->class_id);

        $assignment->load(['kelas.course', 'submissions.student']);

        return view('assistant.assignments.show', compact('assignment'));
    }

    public function edit(Assignment $assignment): View
    {
        $this->assistantClassOrFail((int) $assignment->class_id);

        $assignment->loadMissing(['kelas.course.studySemester']);

        return view('assistant.assignments.edit', [
            'assignment' => $assignment,
            'classes' => $this->assistantClassesQuery()->with(['course.studySemester'])->get(),
            'selectedClass' => $assignment->kelas,
        ]);
    }

    public function update(Request $request, Assignment $assignment, FileTextExtractor $fileTextExtractor): RedirectResponse
    {
        $this->assistantClassOrFail((int) $assignment->class_id);

        $wasPublished = $this->assignmentShouldAppearNow($assignment);

        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            'file' => [
                'nullable',
                'file',
                'mimes:' . self::ALLOWED_ASSIGNMENT_FILE_MIMES,
                'max:' . self::MAX_ASSIGNMENT_FILE_SIZE_KB,
            ],

            'deadline' => ['required', 'date'],
            'max_score' => ['required', 'integer', 'min:1', 'max:1000'],
            'published_at' => ['nullable', 'date', 'before_or_equal:deadline'],
        ], $this->validationMessages());

        $class = $this->assistantClassOrFail((int) $validated['class_id']);

        $oldFilePath = $assignment->file_path;
        $newFilePath = null;
        $filePath = $oldFilePath;
        $extractedText = $assignment->extracted_text;

        if ($request->hasFile('file')) {
            $newFilePath = $request->file('file')->store('assignments', 'public');
            $filePath = $newFilePath;
            $extractedText = $fileTextExtractor->extractFromStoragePath($newFilePath, 'public');
        }

        $publishedAt = $request->filled('published_at')
            ? Carbon::parse($validated['published_at'], config('app.timezone'))
            : null;

        try {
            $assignment->update([
                'class_id' => $class->id,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'file_path' => $filePath,
                'extracted_text' => $extractedText,
                'deadline' => Carbon::parse($validated['deadline'], config('app.timezone')),
                'max_score' => $validated['max_score'],
                'published_at' => $publishedAt,
            ]);
        } catch (Throwable $exception) {
            if ($newFilePath) {
                Storage::disk('public')->delete($newFilePath);
            }

            throw $exception;
        }

        if ($newFilePath && $oldFilePath && $oldFilePath !== $newFilePath) {
            Storage::disk('public')->delete($oldFilePath);
        }

        $assignment->refresh();

        $isPublishedNow = $this->assignmentShouldAppearNow($assignment);

        if (! $wasPublished && $isPublishedNow && ! $assignment->published_notification_sent_at) {
            $this->sendAssignmentCreatedNotification($assignment);

            $assignment->update([
                'published_notification_sent_at' => now(),
            ]);
        }

        return redirect()
            ->route('assistant.courses.show', $class)
            ->with('success', $this->assignmentSuccessMessage('Tugas berhasil diperbarui.', $filePath, $extractedText));
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $class = $this->assistantClassOrFail((int) $assignment->class_id);

        if ($assignment->file_path) {
            Storage::disk('public')->delete($assignment->file_path);
        }

        $assignment->delete();

        return redirect()
            ->route('assistant.courses.show', $class)
            ->with('success', 'Tugas berhasil dihapus.');
    }

    private function selectedAssistantClassFromRequest(Request $request)
    {
        if (! $request->filled('class_id')) {
            return null;
        }

        return $this->assistantClassesQuery()
            ->with(['course.studySemester'])
            ->find($request->integer('class_id'));
    }

    private function assignmentShouldAppearNow(Assignment $assignment): bool
    {
        return $assignment->published_at === null || $assignment->published_at->lte(now());
    }

    private function sendAssignmentCreatedNotification(Assignment $assignment): void
    {
        $assignment->loadMissing(['kelas.course']);

        $class = $assignment->kelas;

        if (! $class) {
            return;
        }

        $classInfo = $this->classContext($class);

        $this->notifyUsers(
            $this->classStudents($class),
            'assignment_created',
            'Tugas Baru',
            "{$assignment->title} telah dibuat untuk {$classInfo['label']}.",
            [
                'assignment_id' => $assignment->id,
                'class_id' => $class->id,
                'course_id' => $class->course_id,
                'course_name' => $classInfo['course_name'],
                'course_code' => $classInfo['course_code'],
                'class_name' => $classInfo['class_name'],
                'context_label' => $classInfo['label'],
                'deadline' => $assignment->deadline?->timezone(config('app.timezone', 'Asia/Jakarta'))->format('d M Y H:i') . ' WIB',
                'url' => route('student.assignments.show', $assignment),
            ]
        );
    }

    private function assignmentSuccessMessage(string $baseMessage, ?string $filePath, ?string $extractedText): string
    {
        if (! $filePath) {
            return $baseMessage;
        }

        if (filled($extractedText)) {
            return $baseMessage . ' File berhasil diunggah dan isi file berhasil dibaca oleh AI.';
        }

        return $baseMessage . ' File berhasil diunggah, tetapi isi file belum bisa dibaca oleh AI. Pastikan file berisi teks yang bisa diseleksi, bukan scan/gambar. Format yang disarankan: PDF teks, DOCX, TXT, MD, atau CSV.';
    }

    private function validationMessages(): array
    {
        return [
            'class_id.required' => 'Kelas praktikum wajib dipilih.',
            'class_id.exists' => 'Kelas praktikum tidak valid.',

            'title.required' => 'Judul tugas wajib diisi.',
            'title.max' => 'Judul tugas maksimal 255 karakter.',

            'file.uploaded' => 'File gagal diunggah. Biasanya karena ukuran file melebihi upload_max_filesize/post_max_size di php.ini, atau file terlalu besar.',
            'file.file' => 'Upload harus berupa file yang valid.',
            'file.mimes' => 'Format file tugas harus PDF, DOCX, TXT, MD, atau CSV agar bisa dibaca oleh AI.',
            'file.max' => 'Ukuran file maksimal 100 MB.',

            'deadline.required' => 'Deadline tugas wajib diisi.',
            'deadline.date' => 'Deadline harus berupa tanggal dan jam yang valid.',
            'deadline.after' => 'Deadline tugas harus setelah waktu sekarang.',

            'max_score.required' => 'Nilai maksimal wajib diisi.',
            'max_score.integer' => 'Nilai maksimal harus berupa angka.',
            'max_score.min' => 'Nilai maksimal minimal 1.',
            'max_score.max' => 'Nilai maksimal maksimal 1000.',

            'published_at.date' => 'Waktu publikasi harus berupa tanggal dan jam yang valid.',
            'published_at.before_or_equal' => 'Waktu publikasi tidak boleh melebihi deadline tugas.',
        ];
    }
}
