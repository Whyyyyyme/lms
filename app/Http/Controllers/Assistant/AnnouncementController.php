<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Concerns\HandlesLmsNotifications;
use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnnouncementController extends Controller
{
    use HandlesLmsNotifications, ResolvesClassAccess;

    public function index(): View
    {
        $announcements = Announcement::query()
            ->with(['kelas.course', 'creator'])
            ->whereIn('class_id', $this->assistantClassesQuery()->pluck('id'))
            ->latest()
            ->paginate(10);

        return view('assistant.announcements.index', compact('announcements'));
    }

    public function create(): View
    {
        return view('assistant.announcements.create', [
            'classes' => $this->assistantClassesQuery()->with('course')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $class = $this->assistantClassOrFail((int) $validated['class_id']);

        $announcement = Announcement::create([
            'class_id' => $class->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
            'created_by' => auth()->id(),
        ]);

        $classInfo = $this->classContext($class);

$this->notifyUsers(
    $this->classStudents($class),
    'announcement_published',
    'Pengumuman Baru',
    "Pengumuman {$announcement->title} untuk {$classInfo['label']}.",
    [
        'announcement_id' => $announcement->id,
        'class_id' => $class->id,
        'course_name' => $classInfo['course_name'],
        'course_code' => $classInfo['course_code'],
        'class_name' => $classInfo['class_name'],
        'context_label' => $classInfo['label'],
        'url' => route('student.dashboard'),
    ]
);

        return redirect()->route('assistant.pengumuman.index')->with('success', 'Pengumuman berhasil dikirim.');
    }

    public function show(Announcement $announcement): View
    {
        $this->assistantClassOrFail((int) $announcement->class_id);
        $announcement->load(['kelas.course', 'creator']);

        return view('assistant.announcements.show', compact('announcement'));
    }

    public function edit(Announcement $announcement): View
    {
        $this->assistantClassOrFail((int) $announcement->class_id);

        return view('assistant.announcements.edit', [
            'announcement' => $announcement,
            'classes' => $this->assistantClassesQuery()->with('course')->get(),
        ]);
    }

    public function update(Request $request, Announcement $announcement): RedirectResponse
    {
        $this->assistantClassOrFail((int) $announcement->class_id);

        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
        ]);

        $class = $this->assistantClassOrFail((int) $validated['class_id']);

        $announcement->update([
            'class_id' => $class->id,
            'title' => $validated['title'],
            'content' => $validated['content'],
        ]);

        return redirect()->route('assistant.pengumuman.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }

    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->assistantClassOrFail((int) $announcement->class_id);
        $announcement->delete();

        return redirect()->route('assistant.pengumuman.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}
