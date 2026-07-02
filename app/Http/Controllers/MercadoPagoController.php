<?php

namespace App\Http\Controllers;

use App\Models\Pedido;
use App\Services\MercadoPagoService;
use App\Services\PedidoService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MercadoPagoController extends Controller
{
    public function __construct(
        private readonly MercadoPagoService $mercadoPago,
        private readonly PedidoService $pedidos,
    ) {}

    /**
     * Webhook de notificaciones de pago de Mercado Pago. Público (sin auth):
     * la firma real se valida consultando el pago con el access token.
     */
    public function webhook(Request $request): Response
    {
        $paymentId = $request->input('data.id') ?? $request->query('id');

        if (! $paymentId || ! $this->mercadoPago->enabled()) {
            return response()->noContent();
        }

        $pago = $this->mercadoPago->consultarPago((string) $paymentId);

        if (! $pago) {
            return response()->noContent();
        }

        $pedido = Pedido::where('numero_pedido', $pago['external_reference'] ?? null)->first();

        if (! $pedido) {
            return response()->noContent();
        }

        $pedido->forceFill(['mp_payment_id' => (string) $paymentId])->save();

        if (($pago['status'] ?? null) === 'approved') {
            $this->pedidos->marcarComoPagado($pedido);
        }

        return response()->noContent();
    }
}
