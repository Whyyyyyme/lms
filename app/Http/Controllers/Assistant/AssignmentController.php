<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Concerns\HandlesLmsNotifications;
use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Assignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,zip,rar', 'max:20480'],
            'deadline' => ['required', 'date', 'after:now'],
            'max_score' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $class = $this->assistantClassOrFail((int) $validated['class_id']);
        $filePath = $request->hasFile('file') ? $request->file('file')->store('assignments', 'public') : null;

        $assignment = Assignment::create([
            'class_id' => $class->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path' => $filePath,
            'deadline' => $validated['deadline'],
            'max_score' => $validated['max_score'],
            'created_by' => auth()->id(),
        ]);

        $this->notifyUsers(
            $class->students,
            'assignment_created',
            'Tugas Baru Dibuat',
            "Tugas {$assignment->title} telah dibuat. Deadline: {$assignment->deadline->format('d/m/Y H:i')}.",
            ['assignment_id' => $assignment->id, 'class_id' => $class->id]
        );

        return redirect()->route('assistant.tugas.index')->with('success', 'Tugas berhasil dibuat.');
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

        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => ['nullable', 'file', 'mimes:pdf,doc,docx,ppt,pptx,zip,rar', 'max:20480'],
            'deadline' => ['required', 'date'],
            'max_score' => ['required', 'integer', 'min:1', 'max:1000'],
        ]);

        $class = $this->assistantClassOrFail((int) $validated['class_id']);
        $filePath = $assignment->file_path;

        if ($request->hasFile('file')) {
            if ($filePath) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $request->file('file')->store('assignments', 'public');
        }

        $assignment->update([
            'class_id' => $class->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'file_path' => $filePath,
            'deadline' => $validated['deadline'],
            'max_score' => $validated['max_score'],
        ]);

        return redirect()->route('assistant.tugas.index')->with('success', 'Tugas berhasil diperbarui.');
    }

    public function destroy(Assignment $assignment): RedirectResponse
    {
        $this->assistantClassOrFail((int) $assignment->class_id);

        if ($assignment->file_path) {
            Storage::disk('public')->delete($assignment->file_path);
        }

        $assignment->delete();

        return redirect()->route('assistant.tugas.index')->with('success', 'Tugas berhasil dihapus.');
    }
}
