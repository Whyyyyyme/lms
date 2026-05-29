<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Concerns\HandlesLmsNotifications;
use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaterialController extends Controller
{
    use HandlesLmsNotifications, ResolvesClassAccess;

    public function index(): View
    {
        $materials = Material::query()
            ->with(['kelas.course', 'creator'])
            ->whereIn('class_id', $this->assistantClassesQuery()->pluck('id'))
            ->latest()
            ->paginate(10);

        return view('assistant.materials.index', compact('materials'));
    }

    public function create(): View
    {
        return view('assistant.materials.create', [
            'classes' => $this->assistantClassesQuery()->with('course')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['pdf', 'video', 'dokumen', 'link'])],
            'file' => ['nullable', 'file', 'max:102400'],
            'link' => ['nullable', 'url', 'max:255'],
            'published_at' => ['nullable', 'date'],
        ], [
            'file.uploaded' => 'File gagal diunggah. Biasanya karena ukuran file melebihi upload_max_filesize/post_max_size di php.ini, atau file terlalu besar.',
            'file.max' => 'Ukuran file maksimal 100 MB.',
            'file.file' => 'Upload harus berupa file yang valid.',
        ]);

        $class = $this->assistantClassOrFail((int) $validated['class_id']);

        $filePath = null;
        if ($request->hasFile('file')) {
            $filePath = $request->file('file')->store('materials', 'public');
        } elseif ($validated['type'] === 'link') {
            $filePath = $validated['link'] ?? null;
        }

        $material = Material::create([
            'class_id' => $class->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'file_path' => $filePath,
            'created_by' => auth()->id(),
            'published_at' => $validated['published_at'] ?? now(),
        ]);

        $this->notifyUsers(
            $class->students,
            'material_uploaded',
            'Materi Baru Diunggah',
            "Materi {$material->title} telah tersedia di {$class->name}.",
            ['material_id' => $material->id, 'class_id' => $class->id]
        );

        return redirect()->route('assistant.materi.index')->with('success', 'Materi berhasil diunggah.');
    }

    public function show(Material $material): View
    {
        $this->assistantClassOrFail((int) $material->class_id);
        $material->load(['kelas.course', 'creator']);

        return view('assistant.materials.show', compact('material'));
    }

    public function edit(Material $material): View
    {
        $this->assistantClassOrFail((int) $material->class_id);

        return view('assistant.materials.edit', [
            'material' => $material,
            'classes' => $this->assistantClassesQuery()->with('course')->get(),
        ]);
    }

    public function update(Request $request, Material $material): RedirectResponse
    {
        $this->assistantClassOrFail((int) $material->class_id);

        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['pdf', 'video', 'dokumen', 'link'])],
            'file' => ['nullable', 'file', 'max:102400'],
            'link' => ['nullable', 'url', 'max:255'],
            'published_at' => ['nullable', 'date'],
        ], [
            'file.uploaded' => 'File gagal diunggah. Biasanya karena ukuran file melebihi upload_max_filesize/post_max_size di php.ini, atau file terlalu besar.',
            'file.max' => 'Ukuran file maksimal 100 MB.',
            'file.file' => 'Upload harus berupa file yang valid.',
        ]);

        $class = $this->assistantClassOrFail((int) $validated['class_id']);
        $filePath = $material->file_path;

        if ($request->hasFile('file')) {
            if ($filePath && ! str_starts_with($filePath, 'http')) {
                Storage::disk('public')->delete($filePath);
            }
            $filePath = $request->file('file')->store('materials', 'public');
        } elseif ($validated['type'] === 'link' && filled($validated['link'] ?? null)) {
            $filePath = $validated['link'];
        }

        $material->update([
            'class_id' => $class->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'file_path' => $filePath,
            'published_at' => $validated['published_at'] ?? $material->published_at,
        ]);

        return redirect()->route('assistant.materi.index')->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Material $material): RedirectResponse
    {
        $this->assistantClassOrFail((int) $material->class_id);

        if ($material->file_path && ! str_starts_with($material->file_path, 'http')) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return redirect()->route('assistant.materi.index')->with('success', 'Materi berhasil dihapus.');
    }
}
