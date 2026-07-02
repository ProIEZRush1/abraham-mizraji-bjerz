<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloquea las acciones con las que el negocio "gana dinero" (checkout, cobros,
 * confirmación de pedidos, publicar en vivo…) mientras el sistema esté en modo
 * prueba. Ver trial_locked() / config/trial.php: se desactiva por completo
 * cambiando TRIAL_LOCKED=false, sin tocar código.
 */
class EnsureTrialIsUnlocked
{
    public function handle(Request $request, Closure $next): Response
    {
        if (trial_locked()) {
            if ($request->expectsJson() || $request->inertia()) {
                return back()->with('trial_locked', true)->with(
                    'error',
                    'Esta función se activa al confirmar tu proyecto con el anticipo. Mientras tanto puedes ver y configurar todo.'
                );
            }

            return response()->json([
                'message' => 'Esta función se activa al confirmar tu proyecto con el anticipo.',
            ], 423);
        }

        return $next($request);
    }
}
