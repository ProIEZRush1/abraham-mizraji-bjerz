<?php

namespace App\Http\Controllers;

use App\Models\CarritoItem;
use App\Models\Producto;
use App\Models\ProductoVariante;
use App\Services\CarritoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CarritoController extends Controller
{
    public function __construct(private readonly CarritoService $carritos) {}

    public function index(Request $request): Response
    {
        $carrito = $this->carritos->actual($request);
        $carrito->load(['items.producto', 'items.variante']);

        return Inertia::render('Carrito/Index', [
            'items' => $carrito->items->map(fn (CarritoItem $item) => [
                'id' => $item->id,
                'producto_id' => $item->producto_id,
                'nombre' => $item->producto?->nombre,
                'slug' => $item->producto?->slug,
                'imagen' => $item->producto?->imagen_principal,
                'variante' => $item->variante?->nombre,
                'precio_unitario' => (float) $item->precio_unitario,
                'cantidad' => $item->cantidad,
                'subtotal' => round($item->cantidad * (float) $item->precio_unitario, 2),
                'stock_disponible' => $item->variante ? $item->variante->stock : ($item->producto?->stock ?? 0),
            ]),
            'subtotal' => $carrito->subtotal(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'producto_id' => ['required', 'exists:productos,id'],
            'producto_variante_id' => ['nullable', 'exists:producto_variantes,id'],
            'cantidad' => ['required', 'integer', 'min:1', 'max:99'],
        ]);

        $producto = Producto::findOrFail($data['producto_id']);
        $variante = $data['producto_variante_id'] ?? null
            ? ProductoVariante::where('producto_id', $producto->id)->findOrFail($data['producto_variante_id'])
            : null;

        $disponible = $variante ? $variante->stock : $producto->stock;

        if ($disponible < 1) {
            return back()->with('error', 'Ese producto no tiene stock disponible.');
        }

        $carrito = $this->carritos->actual($request);

        $item = $carrito->items()
            ->where('producto_id', $producto->id)
            ->where('producto_variante_id', $variante?->id)
            ->first();

        $cantidadFinal = ($item?->cantidad ?? 0) + $data['cantidad'];
        $cantidadFinal = min($cantidadFinal, $disponible);

        $precio = $producto->precioFinal() + (float) ($variante?->precio_extra ?? 0);

        if ($item) {
            $item->update(['cantidad' => $cantidadFinal, 'precio_unitario' => $precio]);
        } else {
            $carrito->items()->create([
                'producto_id' => $producto->id,
                'producto_variante_id' => $variante?->id,
                'cantidad' => $cantidadFinal,
                'precio_unitario' => $precio,
            ]);
        }

        return back()->with('success', 'Producto agregado al carrito.');
    }

    public function update(Request $request, CarritoItem $item): RedirectResponse
    {
        $this->autorizar($request, $item);

        $data = $request->validate(['cantidad' => ['required', 'integer', 'min:1', 'max:99']]);

        $disponible = $item->variante ? $item->variante->stock : $item->producto->stock;
        $item->update(['cantidad' => min($data['cantidad'], max($disponible, 1))]);

        return back()->with('success', 'Carrito actualizado.');
    }

    public function destroy(Request $request, CarritoItem $item): RedirectResponse
    {
        $this->autorizar($request, $item);

        $item->delete();

        return back()->with('success', 'Producto quitado del carrito.');
    }

    private function autorizar(Request $request, CarritoItem $item): void
    {
        $carrito = $this->carritos->actual($request);

        abort_unless($item->carrito_id === $carrito->id, 403);
    }
}
