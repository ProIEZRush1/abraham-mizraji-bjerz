<?php

namespace App\Http\Controllers;

use App\Models\PagosPayment;
use App\Services\PagosPaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PagosPaymentController extends Controller
{
    public function __construct(private readonly PagosPaymentService $payments) {}

    /**
     * Listado de cobros + formulario para generar enlaces de pago.
     */
    public function index(): InertiaResponse
    {
        return Inertia::render('Pagos/Index', [
            'payments' => PagosPayment::query()
                ->latest()
                ->take(100)
                ->get()
                ->map(fn (PagosPayment $p) => [
                    'id' => $p->id,
                    'description' => $p->description,
                    'customer_name' => $p->customer_name,
                    'customer_email' => $p->customer_email,
                    'amount' => $p->amount,
                    'currency' => strtoupper($p->currency),
                    'status' => $p->status,
                    'checkout_url' => $p->checkout_url,
                    'paid_at' => $p->paid_at?->toIso8601String(),
                    'created_at' => $p->created_at?->toIso8601String(),
                ]),
            'stripeEnabled' => $this->payments->enabled(),
            'currency' => strtoupper($this->payments->currency()),
        ]);
    }

    /**
     * Registra un cobro y, si Stripe está activo, genera el enlace de pago.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'description' => ['required', 'string', 'max:255'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_email' => ['nullable', 'email', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
        ]);

        $payment = PagosPayment::create([
            'description' => $data['description'],
            'customer_name' => $data['customer_name'] ?? null,
            'customer_email' => $data['customer_email'] ?? null,
            'amount' => (int) round(((float) $data['amount']) * 100),
            'currency' => strtolower($this->payments->currency()),
            'status' => 'pendiente',
        ]);

        if (! $this->payments->enabled()) {
            return redirect()
                ->route('pagos.index')
                ->with('warning', 'El cobro se registró, pero los pagos en línea están desactivados. Configura STRIPE_SECRET para generar enlaces de pago.');
        }

        $url = $this->payments->createCheckoutLink(
            $payment,
            route('pagos.index'),
            route('pagos.index'),
        );

        if (! $url) {
            return redirect()
                ->route('pagos.index')
                ->with('warning', 'El cobro se registró, pero no se pudo generar el enlace de pago. Inténtalo de nuevo más tarde.');
        }

        return redirect()
            ->route('pagos.index')
            ->with('success', 'Enlace de pago generado correctamente.');
    }

    /**
     * Webhook público de Stripe (sin auth ni CSRF).
     */
    public function webhook(Request $request): Response
    {
        $event = $this->payments->parseWebhookEvent(
            $request->getContent(),
            $request->header('Stripe-Signature'),
        );

        if (! $event) {
            return response('invalid', 400);
        }

        $this->payments->handleWebhookEvent($event);

        return response('ok', 200);
    }
}
