<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Services\PedidoService;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PedidoController extends Controller
{
    public function __construct(private readonly PedidoService $pedidos) {}

    public function index(Request $request): Response
    {
        $query = Pedido::with('user:id,name,email');

        if ($estado = $request->string('estado')->toString()) {
            $query->where('estado', $estado);
        }

        if ($busqueda = $request->string('buscar')->trim()->toString()) {
            $query->where(function ($q) use ($busqueda) {
                $q->where('numero_pedido', 'like', "%{$busqueda}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', "%{$busqueda}%"));
            });
        }

        $pedidos = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return Inertia::render('Pedidos/Index', [
            'pedidos' => $pedidos->through(fn (Pedido $pedido) => [
                'id' => $pedido->id,
                'numero_pedido' => $pedido->numero_pedido,
                'cliente' => $pedido->user?->name,
                'estado' => $pedido->estado,
                'total' => (float) $pedido->total,
                'created_at' => $pedido->created_at->translatedFormat('d M Y, H:i'),
            ]),
            'estados' => Pedido::ESTADOS,
            'filtros' => $request->only(['estado', 'buscar']),
        ]);
    }

    public function show(Pedido $pedido): Response
    {
        $pedido->load('items', 'user', 'zonaEnvio', 'cupon');

        return Inertia::render('Pedidos/Show', [
            'pedido' => [
                'id' => $pedido->id,
                'numero_pedido' => $pedido->numero_pedido,
                'estado' => $pedido->estado,
                'cliente' => ['nombre' => $pedido->user?->name, 'email' => $pedido->user?->email],
                'subtotal' => (float) $pedido->subtotal,
                'descuento' => (float) $pedido->descuento,
                'costo_envio' => (float) $pedido->costo_envio,
                'total' => (float) $pedido->total,
                'cupon_codigo' => $pedido->cupon_codigo,
                'metodo_pago' => $pedido->metodo_pago,
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
            'estados' => Pedido::ESTADOS,
        ]);
    }

    public function actualizarEstado(Request $request, Pedido $pedido): RedirectResponse
    {
        $data = $request->validate(['estado' => ['required', 'in:'.implode(',', Pedido::ESTADOS)]]);

        $this->pedidos->cambiarEstado($pedido, $data['estado']);

        return back()->with('success', 'Estado del pedido actualizado.');
    }
}
