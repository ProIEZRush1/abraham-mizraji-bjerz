<?php

namespace App\Services;

use App\Mail\PedidoConfirmadoMail;
use App\Mail\PedidoEstadoActualizadoMail;
use App\Models\Pedido;
use Illuminate\Support\Facades\Mail;

/**
 * Reglas de negocio del ciclo de vida de un pedido: cambios de estado,
 * descuento de stock al confirmarse el pago y notificaciones por email.
 */
class PedidoService
{
    public function marcarComoPagado(Pedido $pedido): void
    {
        if ($pedido->estado === 'pagado') {
            return;
        }

        $this->descontarStock($pedido);

        $pedido->forceFill([
            'estado' => 'pagado',
            'pagado_at' => now(),
        ])->save();

        $this->notificar($pedido, confirmacionDePago: true);
    }

    public function cambiarEstado(Pedido $pedido, string $estado): void
    {
        if (! in_array($estado, Pedido::ESTADOS, true)) {
            return;
        }

        if ($estado === 'pagado' && $pedido->estado !== 'pagado') {
            $this->marcarComoPagado($pedido);

            return;
        }

        $pedido->update(['estado' => $estado]);
        $this->notificar($pedido, confirmacionDePago: false);
    }

    private function descontarStock(Pedido $pedido): void
    {
        foreach ($pedido->items as $item) {
            if ($item->producto_variante_id) {
                $variante = $item->variante;
                $variante?->decrement('stock', $item->cantidad);

                continue;
            }

            $item->producto?->decrement('stock', $item->cantidad);
        }
    }

    private function notificar(Pedido $pedido, bool $confirmacionDePago): void
    {
        if (! $pedido->user?->email) {
            return;
        }

        try {
            $mailable = $confirmacionDePago
                ? new PedidoConfirmadoMail($pedido)
                : new PedidoEstadoActualizadoMail($pedido);

            Mail::to($pedido->user->email)->send($mailable);
        } catch (\Throwable) {
            // El correo nunca debe interrumpir el flujo de negocio.
        }
    }
}
