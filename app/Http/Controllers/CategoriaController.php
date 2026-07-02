<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CategoriaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Categorias/Index', [
            'categorias' => Categoria::withCount('productos')
                ->orderBy('nombre')
                ->get()
                ->map(fn (Categoria $categoria) => [
                    'id' => $categoria->id,
                    'nombre' => $categoria->nombre,
                    'slug' => $categoria->slug,
                    'descripcion' => $categoria->descripcion,
                    'imagen' => $categoria->imagen,
                    'activo' => $categoria->activo,
                    'productos_count' => $categoria->productos_count,
                ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['nombre']);

        Categoria::create($data);

        return back()->with('success', 'Categoría creada correctamente.');
    }

    public function update(Request $request, Categoria $categoria): RedirectResponse
    {
        $data = $this->validated($request);

        if ($data['nombre'] !== $categoria->nombre) {
            $data['slug'] = $this->uniqueSlug($data['nombre'], $categoria->id);
        }

        $categoria->update($data);

        return back()->with('success', 'Categoría actualizada correctamente.');
    }

    public function destroy(Categoria $categoria): RedirectResponse
    {
        if ($categoria->productos()->exists()) {
            return back()->with('error', 'No podés eliminar una categoría con productos asociados.');
        }

        $categoria->delete();

        return back()->with('success', 'Categoría eliminada.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'descripcion' => ['nullable', 'string', 'max:1000'],
            'imagen' => ['nullable', 'string', 'max:2048'],
            'activo' => ['boolean'],
        ]);
    }

    private function uniqueSlug(string $nombre, ?int $ignoreId = null): string
    {
        $base = Str::slug($nombre);
        $slug = $base;
        $i = 1;

        while (Categoria::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
