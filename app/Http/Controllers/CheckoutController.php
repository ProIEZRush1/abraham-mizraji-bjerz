<?php

namespace App\Http\Controllers;

use App\Models\Cupon;
use App\Models\Pedido;
use App\Models\ZonaEnvio;
use App\Services\CarritoService;
use App\Services\MercadoPagoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CarritoService $carritos,
        private readonly MercadoPagoService $mercadoPago,
    ) {}

    public function index(Request $request): Response|RedirectResponse
    {
        $carrito = $this->carritos->actual($request);
        $carrito->load('items.producto', 'items.variante');

        if ($carrito->items->isEmpty()) {
            return redirect()->route('carrito.index')->with('error', 'Tu carrito está vacío.');
        }

        return Inertia::render('Checkout/Index', [
            'items' => $carrito->items->map(fn ($item) => [
                'nombre' => $item->producto?->nombre,
                'variante' => $item->variante?->nombre,
                'cantidad' => $item->cantidad,
                'subtotal' => round($item->cantidad * (float) $item->precio_unitario, 2),
            ]),
            'subtotal' => $carrito->subtotal(),
            'direcciones' => $request->user()->direcciones()->orderByDesc('predeterminada')->get(),
            'zonas' => ZonaEnvio::where('activo', true)->orderBy('costo')->get(['id', 'nombre', 'costo', 'tiempo_estimado']),
            'mercadoPagoDisponible' => $this->mercadoPago->enabled(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'direccion_id' => ['nullable', 'exists:direcciones,id'],
            'direccion_nueva' => ['nullable', 'array'],
            'direccion_nueva.destinatario' => ['required_without:direccion_id', 'string', 'max:120'],
            'direccion_nueva.calle' => ['required_without:direccion_id', 'string', 'max:150'],
            'direccion_nueva.numero' => ['required_without:direccion_id', 'string', 'max:20'],
            'direccion_nueva.piso_depto' => ['nullable', 'string', 'max:40'],
            'direccion_nueva.ciudad' => ['required_without:direccion_id', 'string', 'max:100'],
            'direccion_nueva.provincia' => ['required_without:direccion_id', 'string', 'max:100'],
            'direccion_nueva.codigo_postal' => ['required_without:direccion_id', 'string', 'max:20'],
            'direccion_nueva.telefono' => ['nullable', 'string', 'max:30'],
            'zona_envio_id' => ['required', 'exists:zonas_envio,id'],
            'cupon_codigo' => ['nullable', 'string', 'max:40'],
        ]);

        $carrito = $this->carritos->actual($request);
        $carrito->load('items.producto', 'items.variante');

        if ($carrito->items->isEmpty()) {
            return redirect()->route('carrito.index')->with('error', 'Tu carrito está vacío.');
        }

        foreach ($carrito->items as $item) {
            $disponible = $item->variante ? $item->variante->stock : $item->producto->stock;

            if ($item->cantidad > $disponible) {
                return back()->with('error', "No hay suficiente stock de {$item->producto->nombre}.");
            }
        }

        $envio = ZonaEnvio::findOrFail($data['zona_envio_id']);
        $subtotal = $carrito->subtotal();

        $cupon = null;
        $descuento = 0.0;

        if (! empty($data['cupon_codigo'])) {
            $cupon = Cupon::where('codigo', strtoupper($data['cupon_codigo']))->first();

            if (! $cupon || ! $cupon->esValidoPara($subtotal)) {
                return back()->with('error', 'El cupón ingresado no es válido.');
            }

            $descuento = $cupon->calcularDescuento($subtotal);
        }

        $direccion = null;

        if (! empty($data['direccion_id'])) {
            $direccion = $request->user()->direcciones()->findOrFail($data['direccion_id']);
        }

        $envioDatos = $direccion
            ? $direccion->only(['destinatario', 'calle', 'numero', 'piso_depto', 'ciudad', 'provincia', 'codigo_postal', 'telefono'])
            : $data['direccion_nueva'];

        $total = round($subtotal - $descuento + (float) $envio->costo, 2);

        $pedido = DB::transaction(function () use ($request, $carrito, $direccion, $envio, $cupon, $descuento, $subtotal, $total, $envioDatos) {
            $pedido = Pedido::create([
                'numero_pedido' => Pedido::generarNumero(),
                'user_id' => $request->user()->id,
                'direccion_id' => $direccion?->id,
                'zona_envio_id' => $envio->id,
                'cupon_id' => $cupon?->id,
                'cupon_codigo' => $cupon?->codigo,
                'envio_destinatario' => $envioDatos['destinatario'],
                'envio_calle' => $envioDatos['calle'],
                'envio_numero' => $envioDatos['numero'],
                'envio_piso_depto' => $envioDatos['piso_depto'] ?? null,
                'envio_ciudad' => $envioDatos['ciudad'],
                'envio_provincia' => $envioDatos['provincia'],
                'envio_codigo_postal' => $envioDatos['codigo_postal'],
                'envio_telefono' => $envioDatos['telefono'] ?? null,
                'subtotal' => $subtotal,
                'descuento' => $descuento,
                'costo_envio' => (float) $envio->costo,
                'total' => $total,
                'estado' => 'pendiente',
            ]);

            foreach ($carrito->items as $item) {
                $pedido->items()->create([
                    'producto_id' => $item->producto_id,
                    'producto_variante_id' => $item->producto_variante_id,
                    'nombre_producto' => $item->producto->nombre,
                    'variante_nombre' => $item->variante?->nombre,
                    'precio_unitario' => $item->precio_unitario,
                    'cantidad' => $item->cantidad,
                    'subtotal' => round($item->cantidad * (float) $item->precio_unitario, 2),
                ]);
            }

            if ($cupon) {
                $cupon->increment('usos_actuales');
            }

            $carrito->items()->delete();

            return $pedido;
        });

        $pedido->load('items');

        $urlPago = $this->mercadoPago->crearPreferencia(
            $pedido,
            route('checkout.exito', $pedido),
            route('carrito.index'),
        );

        if ($urlPago) {
            return Inertia::location($urlPago);
        }

        return redirect()->route('checkout.exito', $pedido);
    }

    public function exito(Request $request, Pedido $pedido): Response
    {
        abort_unless($pedido->user_id === $request->user()->id, 403);

        return Inertia::render('Checkout/Exito', [
            'pedido' => [
                'numero_pedido' => $pedido->numero_pedido,
                'total' => (float) $pedido->total,
                'estado' => $pedido->estado,
            ],
        ]);
    }
}
