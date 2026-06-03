<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Concerns\HandlesLmsNotifications;
use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AssignmentController extends Controller
{
    use HandlesLmsNotifications, ResolvesClassAccess;

    public function index(): View
    {
        $assignments = Assignment::query()
            ->with(['kelas.course', 'creator'])
            ->withCount('submissions')
            ->whereIn('class_id', $this->assistantClassesQuery()->pluck('id'))
            ->latest('deadline')
            ->paginate(10);

        return view('assistant.assignments.index', compact('assignments'));
    }

    public function create(): View
    {
        return view('assistant.assignments.create', [
            'classes' => $this->assistantClassesQuery()->with('course')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:102400'],
            'deadline' => ['required', 'date', 'after:now'],
            'max_score' => ['required', 'integer', 'min:1', 'max:1000'],
            'published_at' => ['nullable', 'date', 'before_or_equal:deadline'],
        ], [
            'file.uploaded' => 'File gagal diunggah. Biasanya karena ukuran file melebihi upload_max_filesize/post_max_size di php.ini, atau file terlalu besar.',
            'file.max' => 'Ukuran file maksimal 100 MB.',
            'file.file' => 'Upload harus berupa file yang valid.',
            'published_at.date' => 'Waktu publikasi harus berupa tanggal dan jam yang valid.',
            'published_at.before_or_equal' => 'Waktu publikasi tidak boleh melebihi deadline tugas.',
        ]);

        $class = $this->assistantClassOrFail((int) $validated['class_id']);

        $filePath = $request->hasFile('file')
            ? $request->file('file')->store('assignments', 'public')
            : null;

        $publishedAt = $request->filled('published_at')
            ? Carbon::parse($validated['published_at'])
            : null;

        $assignment = Assignment::create([
            'class_id' => $class->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path' => $filePath,
            'deadline' => $validated['deadline'],
            'max_score' => $validated['max_score'],
            'created_by' => auth()->id(),
            'published_at' => $publishedAt,
            'published_notification_sent_at' => null,
        ]);

        if ($this->assignmentShouldAppearNow($assignment)) {
            $this->sendAssignmentCreatedNotification($assignment);
        $classInfo = $this->classContext($class);

$this->notifyUsers(
    $this->classStudents($class),
    'assignment_created',
    'Tugas Baru',
    "{$assignment->title} telah dibuat untuk {$classInfo['label']}.",
    [
        'assignment_id' => $assignment->id,
        'class_id' => $class->id,
        'course_name' => $classInfo['course_name'],
        'course_code' => $classInfo['course_code'],
        'class_name' => $classInfo['class_name'],
        'context_label' => $classInfo['label'],
        'deadline' => $assignment->deadline?->timezone('Asia/Jakarta')->format('d M Y H:i') . ' WIB',
        'url' => route('student.assignments.show', $assignment),
    ]
);

            $assignment->update([
                'published_notification_sent_at' => now(),
            ]);
        }

        return redirect()
            ->route('assistant.tugas.index')
            ->with('success', 'Tugas berhasil dibuat.');
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

        return view('assistant.assignments.edit', [
            'assignment' => $assignment,
            'classes' => $this->assistantClassesQuery()->with('course')->get(),
        ]);
    }

    public function update(Request $request, Assignment $assignment): RedirectResponse
    {
        $this->assistantClassOrFail((int) $assignment->class_id);

        $wasPublished = $this->assignmentShouldAppearNow($assignment);

        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'max:102400'],
            'deadline' => ['required', 'date'],
            'max_score' => ['required', 'integer', 'min:1', 'max:1000'],
            'published_at' => ['nullable', 'date', 'before_or_equal:deadline'],
        ], [
            'file.uploaded' => 'File gagal diunggah. Biasanya karena ukuran file melebihi upload_max_filesize/post_max_size di php.ini, atau file terlalu besar.',
            'file.max' => 'Ukuran file maksimal 100 MB.',
            'file.file' => 'Upload harus berupa file yang valid.',
            'published_at.date' => 'Waktu publikasi harus berupa tanggal dan jam yang valid.',
            'published_at.before_or_equal' => 'Waktu publikasi tidak boleh melebihi deadline tugas.',
        ]);

        $class = $this->assistantClassOrFail((int) $validated['class_id']);

        $filePath = $assignment->file_path;

        if ($request->hasFile('file')) {
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }

            $filePath = $request->file('file')->store('assignments', 'public');
        }

        $publishedAt = $request->filled('published_at')
            ? Carbon::parse($validated['published_at'])
            : null;

        $assignment->update([
            'class_id' => $class->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path' => $filePath,
            'deadline' => $validated['deadline'],
            'max_score' => $validated['max_score'],
            'published_at' => $publishedAt,
        ]);

        $assignment->refresh();

        $isPublishedNow = $this->assignmentShouldAppearNow($assignment);

        if (! $wasPublished && $isPublishedNow && ! $assignment->published_notification_sent_at) {
            $this->sendAssignmentCreatedNotification($assignment);

            $assignment->update([
                'published_notification_sent_at' => now(),
            ]);
        }

        return redirect()
            ->route('assistant.tugas.index')
            ->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $this->assistantClassOrFail((int) $assignment->class_id);

        if ($assignment->file_path) {
            Storage::disk('public')->delete($assignment->file_path);
        }

        $assignment->delete();

        return redirect()
            ->route('assistant.tugas.index')
            ->with('success', 'Tugas berhasil dihapus.');
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

        $courseName = $class->course?->name ?? 'Mata kuliah praktikum';

        $this->notifyUsers(
            $this->classStudents($class),
            'assignment_created',
            'Tugas Baru Dibuat',
            "Tugas {$assignment->title} untuk {$courseName} telah dibuat. Deadline: {$assignment->deadline->format('d/m/Y H:i')}.",
            [
                'assignment_id' => $assignment->id,
                'class_id' => $class->id,
                'course_id' => $class->course_id,
                'course_name' => $courseName,
                'deadline' => $assignment->deadline?->toDateTimeString(),
            ]
        );
    }
}