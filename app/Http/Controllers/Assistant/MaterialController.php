<?php

namespace App\Http\Controllers\Assistant;

use App\Http\Controllers\Concerns\HandlesLmsNotifications;
use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Material;
use App\Services\Ai\FileTextExtractor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaterialController extends Controller
{
    use HandlesLmsNotifications, ResolvesClassAccess;

    public function index(Request $request): View
    {
        $selectedClass = $this->selectedAssistantClassFromRequest($request);

        $materials = Material::query()
            ->with(['kelas.course', 'creator'])
            ->whereIn('class_id', $this->assistantClassesQuery()->pluck('id'))
            ->when($selectedClass, fn ($query) => $query->where('class_id', $selectedClass->id))
            ->latest()
            ->paginate(10);

        return view('assistant.materials.index', compact('materials', 'selectedClass'));
    }

    public function create(Request $request): View
    {
        return view('assistant.materials.create', [
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
            'type' => ['required', Rule::in(['pdf', 'link'])],
            'file' => ['required_if:type,pdf', 'nullable', 'file', 'mimes:pdf', 'max:102400'],
            'link' => ['required_if:type,link', 'nullable', 'url', 'max:1000'],
            'published_at' => ['nullable', 'date'],
        ], [
            'type.required' => 'Tipe materi wajib dipilih.',
            'type.in' => 'Tipe materi hanya boleh PDF atau Link Video.',

            'file.required_if' => 'File PDF wajib diupload jika tipe materi adalah PDF.',
            'file.uploaded' => 'File gagal diunggah. Cek upload_max_filesize, post_max_size, dan upload_tmp_dir di php.ini.',
            'file.mimes' => 'File harus berformat PDF.',
            'file.max' => 'Ukuran file maksimal 100 MB.',
            'file.file' => 'Upload harus berupa file yang valid.',

            'link.required_if' => 'Link video wajib diisi jika tipe materi adalah Link Video.',
            'link.url' => 'Link video harus berupa URL yang valid.',
        ]);

        $class = $this->assistantClassOrFail((int) $validated['class_id']);

        $filePath = null;
        $extractedText = null;

        if ($validated['type'] === 'pdf') {
            $filePath = $request->file('file')->store('materials', 'public');
            $extractedText = $fileTextExtractor->extractFromStoragePath($filePath, 'public');
        }

        if ($validated['type'] === 'link') {
            $filePath = $validated['link'];
            $extractedText = null;
        }

        $publishedAt = $request->filled('published_at')
            ? Carbon::parse($validated['published_at'])
            : now();

        $material = Material::create([
            'class_id' => $class->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'file_path' => $filePath,
            'extracted_text' => $extractedText,
            'created_by' => auth()->id(),
            'published_at' => $publishedAt,
            'published_notification_sent_at' => null,
        ]);

        if ($this->materialShouldAppearNow($material)) {
            $this->sendMaterialUploadedNotification($material);

            $material->update([
                'published_notification_sent_at' => now(),
            ]);
        }

        return redirect()
            ->route('assistant.courses.show', $class)
            ->with('success', $this->materialSuccessMessage('Materi berhasil ditambahkan.', $filePath, $extractedText, $validated['type']));
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

        $material->loadMissing(['kelas.course.studySemester']);

        return view('assistant.materials.edit', [
            'material' => $material,
            'classes' => $this->assistantClassesQuery()->with(['course.studySemester'])->get(),
            'selectedClass' => $material->kelas,
        ]);
    }

    public function update(Request $request, Material $material, FileTextExtractor $fileTextExtractor): RedirectResponse
    {
        $this->assistantClassOrFail((int) $material->class_id);

        $wasPublished = $this->materialShouldAppearNow($material);

        $validated = $request->validate([
            'class_id' => ['required', 'exists:classes,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'type' => ['required', Rule::in(['pdf', 'link'])],
            'file' => ['nullable', 'file', 'mimes:pdf', 'max:102400'],
            'link' => ['nullable', 'url', 'max:1000'],
            'published_at' => ['nullable', 'date'],
        ], [
            'type.required' => 'Tipe materi wajib dipilih.',
            'type.in' => 'Tipe materi hanya boleh PDF atau Link Video.',

            'file.uploaded' => 'File gagal diunggah. Cek upload_max_filesize, post_max_size, dan upload_tmp_dir di php.ini.',
            'file.mimes' => 'File harus berformat PDF.',
            'file.max' => 'Ukuran file maksimal 100 MB.',
            'file.file' => 'Upload harus berupa file yang valid.',

            'link.url' => 'Link materi harus berupa URL yang valid.',
        ]);

        $class = $this->assistantClassOrFail((int) $validated['class_id']);

        $filePath = $material->file_path;
        $extractedText = $material->extracted_text;

        if ($validated['type'] === 'pdf') {
            if ($request->hasFile('file')) {
                if ($filePath && ! str_starts_with($filePath, 'http')) {
                    Storage::disk('public')->delete($filePath);
                }

                $filePath = $request->file('file')->store('materials', 'public');
                $extractedText = $fileTextExtractor->extractFromStoragePath($filePath, 'public');
            }

            if (blank($filePath) || str_starts_with((string) $filePath, 'http')) {
                return back()
                    ->withErrors([
                        'file' => 'Silakan upload file PDF untuk materi ini.',
                    ])
                    ->withInput();
            }
        }

        if ($validated['type'] === 'link') {
            if ($filePath && ! str_starts_with($filePath, 'http')) {
                Storage::disk('public')->delete($filePath);
            }

            if (blank($validated['link'] ?? null)) {
                return back()
                    ->withErrors([
                        'link' => 'Silakan isi link materi.',
                    ])
                    ->withInput();
            }

            $filePath = $validated['link'];
            $extractedText = null;
        }

        $publishedAt = $request->filled('published_at')
            ? Carbon::parse($validated['published_at'])
            : $material->published_at;

        $material->update([
            'class_id' => $class->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'file_path' => $filePath,
            'extracted_text' => $extractedText,
            'published_at' => $publishedAt,
        ]);

        $material->refresh();

        $isPublishedNow = $this->materialShouldAppearNow($material);

        if (! $wasPublished && $isPublishedNow && ! $material->published_notification_sent_at) {
            $this->sendMaterialUploadedNotification($material);

            $material->update([
                'published_notification_sent_at' => now(),
            ]);
        }

        return redirect()
            ->route('assistant.courses.show', $class)
            ->with('success', $this->materialSuccessMessage('Materi berhasil diperbarui.', $filePath, $extractedText, $validated['type']));
    }

    public function destroy(Material $material): RedirectResponse
    {
        $class = $this->assistantClassOrFail((int) $material->class_id);

        if ($material->file_path && ! str_starts_with($material->file_path, 'http')) {
            Storage::disk('public')->delete($material->file_path);
        }

        $material->delete();

        return redirect()
            ->route('assistant.courses.show', $class)
            ->with('success', 'Materi berhasil dihapus.');
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

    private function materialShouldAppearNow(Material $material): bool
    {
        return $material->published_at !== null && $material->published_at->lte(now());
    }

    private function sendMaterialUploadedNotification(Material $material): void
    {
        $material->loadMissing(['kelas.course']);

        $class = $material->kelas;

        if (! $class) {
            return;
        }

        $classInfo = $this->classContext($class);

        $this->notifyUsers(
            $this->classStudents($class),
            'material_uploaded',
            'Materi Baru Diunggah',
            "{$material->title} telah tersedia untuk {$classInfo['label']}.",
            [
                'material_id' => $material->id,
                'class_id' => $class->id,
                'course_name' => $classInfo['course_name'],
                'course_code' => $classInfo['course_code'],
                'class_name' => $classInfo['class_name'],
                'context_label' => $classInfo['label'],
                'url' => route('student.materials.show', $material),
            ]
        );
    }

    private function materialSuccessMessage(string $baseMessage, ?string $filePath, ?string $extractedText, string $type): string
    {
        if ($type === 'link') {
            return $baseMessage . ' Link materi berhasil disimpan.';
        }

        if (! $filePath) {
            return $baseMessage;
        }

        if (filled($extractedText)) {
            return $baseMessage . ' Isi file berhasil dibaca oleh AI.';
        }

        return $baseMessage . ' File berhasil diunggah, tetapi isi file belum bisa dibaca oleh AI. Jika file berupa PDF hasil scan/gambar, fitur ini membutuhkan OCR.';
    }
}