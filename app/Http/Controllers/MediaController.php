<?php

namespace App\Http\Controllers;

use App\Models\MediaFile;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    public function __construct(private readonly MediaService $media) {}

    /**
     * Display the media library.
     */
    public function index(): Response
    {
        return Inertia::render('Media/Index', [
            'files' => MediaFile::query()
                ->latest()
                ->get()
                ->map(fn (MediaFile $file) => [
                    'id' => $file->id,
                    'name' => $file->name,
                    'url' => $file->url,
                    'mime_type' => $file->mime_type,
                    'extension' => $file->extension,
                    'is_image' => $file->is_image,
                    'size_human' => $file->size_human,
                    'created_at' => $file->created_at?->toIso8601String(),
                ]),
        ]);
    }

    /**
     * Store a newly uploaded file.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'file' => ['required', 'file', 'max:25600'], // 25 MB
        ], [
            'file.required' => 'Selecciona un archivo para subir.',
            'file.file' => 'El archivo no es válido.',
            'file.max' => 'El archivo supera el tamaño máximo permitido (25 MB).',
        ]);

        $this->media->store($validated['file'], $request->user()?->id);

        return back()->with('success', 'Archivo subido correctamente.');
    }

    /**
     * Remove a file from the library.
     */
    public function destroy(MediaFile $mediaFile): RedirectResponse
    {
        $this->media->delete($mediaFile);

        return back()->with('success', 'Archivo eliminado.');
    }
}
