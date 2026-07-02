<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class MisPedidosController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('MisPedidos/Index', [
            'pedidos' => $request->user()->pedidos()
                ->orderByDesc('id')
                ->get()
                ->map(fn (Pedido $pedido) => [
                    'id' => $pedido->id,
                    'numero_pedido' => $pedido->numero_pedido,
                    'estado' => $pedido->estado,
                    'total' => (float) $pedido->total,
                    'created_at' => $pedido->created_at->translatedFormat('d M Y, H:i'),
                ]),
        ]);
    }

    public function show(Request $request, Pedido $pedido): Response
    {
        abort_unless($pedido->user_id === $request->user()->id, 403);

        $pedido->load('items', 'zonaEnvio');

        return Inertia::render('MisPedidos/Show', [
            'pedido' => [
                'id' => $pedido->id,
                'numero_pedido' => $pedido->numero_pedido,
                'estado' => $pedido->estado,
                'subtotal' => (float) $pedido->subtotal,
                'descuento' => (float) $pedido->descuento,
                'costo_envio' => (float) $pedido->costo_envio,
                'total' => (float) $pedido->total,
                'cupon_codigo' => $pedido->cupon_codigo,
                'created_at' => $pedido->created_at->translatedFormat('d M Y, H:i'),
                'envio' => [
                    'destinatario' => $pedido->envio_destinatario,
                    'calle' => $pedido->envio_calle,
                    'numero' => $pedido->envio_numero,
                    'piso_depto' => $pedido->envio_piso_depto,
                    'ciudad' => $pedido->envio_ciudad,
                    'provincia' => $pedido->envio_provincia,
                    'codigo_postal' => $pedido->envio_codigo_postal,
                    'telefono' => $pedido->envio_telefono,
                    'zona' => $pedido->zonaEnvio?->nombre,
                ],
                'items' => $pedido->items->map(fn ($item) => [
                    'nombre_producto' => $item->nombre_producto,
                    'variante_nombre' => $item->variante_nombre,
                    'precio_unitario' => (float) $item->precio_unitario,
                    'cantidad' => $item->cantidad,
                    'subtotal' => (float) $item->subtotal,
                ]),
            ],
        ]);
    }
}
