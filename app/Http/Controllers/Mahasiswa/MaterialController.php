<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Concerns\ResolvesClassAccess;
use App\Http\Controllers\Controller;
use App\Models\Material;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MaterialController extends Controller
{
    use ResolvesClassAccess;

    public function index(): View
    {
        $materials = Material::query()
            ->with(['kelas.course', 'creator'])
            ->published()
            ->whereIn('class_id', $this->studentClassIds())
            ->latest('published_at')
            ->paginate(10);

        return view('student.materials.index', compact('materials'));
    }

    public function show(Material $material): View
    {
        abort_unless(in_array($material->class_id, $this->studentClassIds(), true), 403);
        abort_if($material->published_at === null || $material->published_at->isFuture(), 404);

        $material->load(['kelas.course', 'creator']);

        return view('student.materials.show', compact('material'));
    }

    public function download(Material $material)
    {
        abort_unless(in_array($material->class_id, $this->studentClassIds(), true), 403);
        abort_if(blank($material->file_path), 404);

        if (str_starts_with($material->file_path, 'http')) {
            return redirect()->away($material->file_path);
        }

        abort_unless(Storage::disk('public')->exists($material->file_path), 404);

        return Storage::disk('public')->download($material->file_path);
    }
}
