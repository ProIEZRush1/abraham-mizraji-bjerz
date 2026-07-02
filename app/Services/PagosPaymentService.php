<?php

namespace App\Services;

use App\Models\PagosPayment;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;
use Stripe\Webhook;

/**
 * Servicio de pagos con Stripe.
 *
 * Encapsula toda la integración con Stripe y degrada de forma elegante:
 * si no hay STRIPE_SECRET configurado (o el SDK no está instalado) ningún
 * método lanza excepciones; simplemente la generación de enlaces queda
 * deshabilitada y la interfaz lo refleja con un mensaje amable.
 */
class PagosPaymentService
{
    private ?string $secret;

    private ?string $webhookSecret;

    private string $currency;

    public function __construct()
    {
        $this->secret = config('pagos.secret') ?: null;
        $this->webhookSecret = config('pagos.webhook_secret') ?: null;
        $this->currency = strtolower((string) config('pagos.currency', 'mxn')) ?: 'mxn';
    }

    /**
     * ¿Está disponible el cobro en línea? Requiere llave secreta y SDK.
     */
    public function enabled(): bool
    {
        return ! empty($this->secret) && class_exists(StripeClient::class);
    }

    public function currency(): string
    {
        return $this->currency;
    }

    /**
     * Crea una sesión de Checkout de Stripe y persiste sus datos en el cobro.
     *
     * Devuelve la URL del enlace de pago, o null si la función está
     * deshabilitada o Stripe devolvió un error (que se registra en el log).
     */
    public function createCheckoutLink(PagosPayment $payment, string $successUrl, string $cancelUrl): ?string
    {
        if (! $this->enabled()) {
            return null;
        }

        try {
            $client = new StripeClient($this->secret);

            $params = [
                'mode' => 'payment',
                'line_items' => [[
                    'price_data' => [
                        'currency' => $payment->currency ?: $this->currency,
                        'product_data' => ['name' => $payment->description],
                        'unit_amount' => (int) $payment->amount,
                    ],
                    'quantity' => 1,
                ]],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'metadata' => ['payment_id' => (string) $payment->id],
            ];

            if (! empty($payment->customer_email)) {
                $params['customer_email'] = $payment->customer_email;
            }

            $session = $client->checkout->sessions->create($params);

            $payment->forceFill([
                'stripe_session_id' => $session->id ?? null,
                'stripe_payment_intent' => $session->payment_intent ?? null,
                'checkout_url' => $session->url ?? null,
            ])->save();

            return $session->url ?? null;
        } catch (\Throwable $e) {
            Log::warning('pagos.checkout_failed', [
                'payment_id' => $payment->id,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Verifica y decodifica el evento del webhook de Stripe.
     *
     * Si hay secreto de webhook configurado se valida la firma; de lo
     * contrario se decodifica el cuerpo sin verificar. Devuelve un objeto
     * tipo evento o null si la firma es inválida / el cuerpo no es válido.
     */
    public function parseWebhookEvent(string $payload, ?string $signature): ?object
    {
        if (! empty($this->webhookSecret) && class_exists(Webhook::class)) {
            try {
                return Webhook::constructEvent($payload, (string) $signature, $this->webhookSecret);
            } catch (\Throwable $e) {
                Log::warning('pagos.webhook_invalid_signature', ['message' => $e->getMessage()]);

                return null;
            }
        }

        $decoded = json_decode($payload);

        return is_object($decoded) ? $decoded : null;
    }

    /**
     * Procesa un evento de webhook ya verificado, actualizando el cobro.
     */
    public function handleWebhookEvent(object $event): void
    {
        $type = $event->type ?? null;

        if ($type !== 'checkout.session.completed') {
            return;
        }

        $session = $event->data->object ?? null;

        if (! $session) {
            return;
        }

        $paymentId = $session->metadata->payment_id ?? null;
        $sessionId = $session->id ?? null;

        $payment = null;

        if ($paymentId) {
            $payment = PagosPayment::find($paymentId);
        }

        if (! $payment && $sessionId) {
            $payment = PagosPayment::where('stripe_session_id', $sessionId)->first();
        }

        if (! $payment) {
            return;
        }

        $payment->forceFill([
            'status' => 'pagado',
            'stripe_payment_intent' => $session->payment_intent ?? $payment->stripe_payment_intent,
            'paid_at' => now(),
        ])->save();
    }
}
