<?php

namespace App\Services;

use App\Models\Pedido;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Integración con Mercado Pago Checkout Pro (API REST directa, sin SDK).
 *
 * Degrada con elegancia: sin MERCADOPAGO_ACCESS_TOKEN configurado, enabled()
 * es false y ningún método intenta llamar a la API real (evita romper el
 * build en el entorno de prueba, que nunca tiene credenciales reales).
 */
class MercadoPagoService
{
    private const API_BASE = 'https://api.mercadopago.com';

    private ?string $accessToken;

    public function __construct()
    {
        $this->accessToken = config('mercadopago.access_token') ?: null;
    }

    public function enabled(): bool
    {
        return ! empty($this->accessToken);
    }

    /**
     * Crea una preferencia de Checkout Pro para el pedido y devuelve la URL
     * de pago (init_point), o null si no está habilitado o falló la llamada.
     */
    public function crearPreferencia(Pedido $pedido, string $successUrl, string $failureUrl): ?string
    {
        if (! $this->enabled()) {
            return null;
        }

        try {
            $items = $pedido->items->map(fn ($item) => [
                'title' => $item->nombre_producto.($item->variante_nombre ? " ({$item->variante_nombre})" : ''),
                'quantity' => (int) $item->cantidad,
                'unit_price' => (float) $item->precio_unitario,
                'currency_id' => 'ARS',
            ])->all();

            $response = Http::withToken($this->accessToken)
                ->post(self::API_BASE.'/checkout/preferences', [
                    'items' => $items,
                    'external_reference' => $pedido->numero_pedido,
                    'back_urls' => [
                        'success' => $successUrl,
                        'failure' => $failureUrl,
                        'pending' => $successUrl,
                    ],
                    'auto_return' => 'approved',
                    'notification_url' => route('mercadopago.webhook'),
                ]);

            if (! $response->successful()) {
                Log::warning('mercadopago.preference_failed', [
                    'pedido_id' => $pedido->id,
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            $data = $response->json();

            $pedido->forceFill([
                'mp_preference_id' => $data['id'] ?? null,
            ])->save();

            return $data['init_point'] ?? null;
        } catch (\Throwable $e) {
            Log::warning('mercadopago.preference_exception', [
                'pedido_id' => $pedido->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Consulta el estado de un pago por su ID ante la API de Mercado Pago.
     */
    public function consultarPago(string $paymentId): ?array
    {
        if (! $this->enabled()) {
            return null;
        }

        try {
            $response = Http::withToken($this->accessToken)
                ->get(self::API_BASE."/v1/payments/{$paymentId}");

            return $response->successful() ? $response->json() : null;
        } catch (\Throwable $e) {
            Log::warning('mercadopago.consulta_exception', ['message' => $e->getMessage()]);

            return null;
        }
    }
}
