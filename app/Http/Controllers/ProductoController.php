<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductoController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Producto::query()->with('categoria');

        if ($busqueda = $request->string('buscar')->trim()->toString()) {
            $query->where('nombre', 'like', "%{$busqueda}%");
        }

        if ($categoriaId = $request->integer('categoria_id')) {
            $query->where('categoria_id', $categoriaId);
        }

        if ($request->string('stock')->toString() === 'bajo') {
            $query->whereColumn('stock', '<=', 'stock_minimo');
        }

        $productos = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return Inertia::render('Productos/Index', [
            'productos' => $productos->through(fn (Producto $producto) => [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'slug' => $producto->slug,
                'categoria' => $producto->categoria?->nombre,
                'precio' => (float) $producto->precio,
                'precio_oferta' => $producto->precio_oferta !== null ? (float) $producto->precio_oferta : null,
                'stock' => $producto->stockDisponible(),
                'stock_minimo' => $producto->stock_minimo,
                'bajo_stock' => $producto->bajoStock(),
                'activo' => $producto->activo,
                'destacado' => $producto->destacado,
                'imagen_principal' => $producto->imagen_principal,
            ]),
            'categorias' => Categoria::orderBy('nombre')->get(['id', 'nombre']),
            'filtros' => $request->only(['buscar', 'categoria_id', 'stock']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Productos/Create', [
            'categorias' => Categoria::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['nombre']);

        $producto = Producto::create($data);

        $this->sincronizarVariantes($producto, $request->input('variantes', []));

        return redirect()->route('productos.index')->with('success', 'Producto creado correctamente.');
    }

    public function edit(Producto $producto): Response
    {
        return Inertia::render('Productos/Edit', [
            'producto' => [
                'id' => $producto->id,
                'categoria_id' => $producto->categoria_id,
                'nombre' => $producto->nombre,
                'descripcion' => $producto->descripcion,
                'precio' => (float) $producto->precio,
                'precio_oferta' => $producto->precio_oferta !== null ? (float) $producto->precio_oferta : null,
                'sku' => $producto->sku,
                'stock' => $producto->stock,
                'stock_minimo' => $producto->stock_minimo,
                'imagen_principal' => $producto->imagen_principal,
                'imagenes' => $producto->imagenes ?? [],
                'activo' => $producto->activo,
                'destacado' => $producto->destacado,
                'variantes' => $producto->variantes()->get(['id', 'nombre', 'sku', 'precio_extra', 'stock', 'activo']),
            ],
            'categorias' => Categoria::orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    public function update(Request $request, Producto $producto): RedirectResponse
    {
        $data = $this->validated($request);

        if ($data['nombre'] !== $producto->nombre) {
            $data['slug'] = $this->uniqueSlug($data['nombre'], $producto->id);
        }

        $producto->update($data);

        $this->sincronizarVariantes($producto, $request->input('variantes', []));

        return redirect()->route('productos.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Producto $producto): RedirectResponse
    {
        $producto->delete();

        return back()->with('success', 'Producto eliminado.');
    }

    public function subirImagen(Request $request): JsonResponse
    {
        $request->validate([
            'imagen' => ['required', 'image', 'max:4096'],
        ]);

        $path = $request->file('imagen')->store('productos', 'public');

        return response()->json(['url' => Storage::disk('public')->url($path)]);
    }

    private function sincronizarVariantes(Producto $producto, array $variantes): void
    {
        $idsRecibidos = [];

        foreach ($variantes as $variante) {
            $payload = [
                'nombre' => $variante['nombre'] ?? '',
                'sku' => $variante['sku'] ?? null,
                'precio_extra' => $variante['precio_extra'] ?? 0,
                'stock' => $variante['stock'] ?? 0,
                'activo' => $variante['activo'] ?? true,
            ];

            if (! trim($payload['nombre'])) {
                continue;
            }

            if (! empty($variante['id'])) {
                $producto->variantes()->where('id', $variante['id'])->update($payload);
                $idsRecibidos[] = (int) $variante['id'];
            } else {
                $nueva = $producto->variantes()->create($payload);
                $idsRecibidos[] = $nueva->id;
            }
        }

        $producto->variantes()->whereNotIn('id', $idsRecibidos)->delete();
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'categoria_id' => ['nullable', 'exists:categorias,id'],
            'nombre' => ['required', 'string', 'max:180'],
            'descripcion' => ['nullable', 'string', 'max:5000'],
            'precio' => ['required', 'numeric', 'min:0'],
            'precio_oferta' => ['nullable', 'numeric', 'min:0', 'lt:precio'],
            'sku' => ['nullable', 'string', 'max:60'],
            'stock' => ['required', 'integer', 'min:0'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
            'imagen_principal' => ['nullable', 'string', 'max:2048'],
            'imagenes' => ['nullable', 'array'],
            'imagenes.*' => ['string', 'max:2048'],
            'activo' => ['boolean'],
            'destacado' => ['boolean'],
        ]);
    }

    private function uniqueSlug(string $nombre, ?int $ignoreId = null): string
    {
        $base = Str::slug($nombre);
        $slug = $base;
        $i = 1;

        while (Producto::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
