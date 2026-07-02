<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TiendaController extends Controller
{
    public function home(): Response
    {
        return Inertia::render('Tienda/Home', [
            'destacados' => Producto::with('categoria')
                ->where('activo', true)
                ->where('destacado', true)
                ->orderByDesc('id')
                ->take(8)
                ->get()
                ->map(fn (Producto $producto) => $this->tarjeta($producto)),
            'categorias' => Categoria::where('activo', true)
                ->withCount('productos')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'slug', 'imagen']),
        ]);
    }

    public function index(Request $request): Response
    {
        $query = Producto::with('categoria')->where('activo', true);

        if ($busqueda = $request->string('buscar')->trim()->toString()) {
            $query->where('nombre', 'like', "%{$busqueda}%");
        }

        if ($categoriaSlug = $request->string('categoria')->toString()) {
            $query->whereHas('categoria', fn ($q) => $q->where('slug', $categoriaSlug));
        }

        if ($precioMin = $request->input('precio_min')) {
            $query->where('precio', '>=', (float) $precioMin);
        }

        if ($precioMax = $request->input('precio_max')) {
            $query->where('precio', '<=', (float) $precioMax);
        }

        if ($request->string('disponibilidad')->toString() === 'en_stock') {
            $query->where('stock', '>', 0);
        }

        $orden = $request->string('orden')->toString();
        match ($orden) {
            'precio_asc' => $query->orderBy('precio'),
            'precio_desc' => $query->orderByDesc('precio'),
            default => $query->orderByDesc('id'),
        };

        $productos = $query->paginate(12)->withQueryString();

        return Inertia::render('Tienda/Index', [
            'productos' => $productos->through(fn (Producto $producto) => $this->tarjeta($producto)),
            'categorias' => Categoria::where('activo', true)->orderBy('nombre')->get(['id', 'nombre', 'slug']),
            'filtros' => $request->only(['buscar', 'categoria', 'precio_min', 'precio_max', 'disponibilidad', 'orden']),
        ]);
    }

    public function show(Producto $producto): Response
    {
        abort_unless($producto->activo, 404);

        $producto->load('categoria', 'variantes');

        return Inertia::render('Tienda/Show', [
            'producto' => [
                'id' => $producto->id,
                'nombre' => $producto->nombre,
                'slug' => $producto->slug,
                'descripcion' => $producto->descripcion,
                'precio' => (float) $producto->precio,
                'precio_oferta' => $producto->precio_oferta !== null ? (float) $producto->precio_oferta : null,
                'precio_final' => $producto->precioFinal(),
                'stock' => $producto->stockDisponible(),
                'imagen_principal' => $producto->imagen_principal,
                'imagenes' => $producto->imagenes ?? [],
                'categoria' => $producto->categoria ? [
                    'nombre' => $producto->categoria->nombre,
                    'slug' => $producto->categoria->slug,
                ] : null,
                'variantes' => $producto->variantes->where('activo', true)->values()->map(fn ($v) => [
                    'id' => $v->id,
                    'nombre' => $v->nombre,
                    'precio_extra' => (float) $v->precio_extra,
                    'stock' => $v->stock,
                ]),
            ],
            'relacionados' => Producto::where('categoria_id', $producto->categoria_id)
                ->where('id', '!=', $producto->id)
                ->where('activo', true)
                ->take(4)
                ->get()
                ->map(fn (Producto $p) => $this->tarjeta($p)),
        ]);
    }

    private function tarjeta(Producto $producto): array
    {
        return [
            'id' => $producto->id,
            'nombre' => $producto->nombre,
            'slug' => $producto->slug,
            'categoria' => $producto->categoria?->nombre,
            'precio' => (float) $producto->precio,
            'precio_oferta' => $producto->precio_oferta !== null ? (float) $producto->precio_oferta : null,
            'precio_final' => $producto->precioFinal(),
            'imagen_principal' => $producto->imagen_principal,
            'en_stock' => $producto->stockDisponible() > 0,
        ];
    }
}
