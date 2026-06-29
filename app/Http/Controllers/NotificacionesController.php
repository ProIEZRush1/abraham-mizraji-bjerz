<?php

namespace App\Http\Controllers;

use App\Models\Notificacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificacionesController extends Controller
{
    /**
     * Página de notificaciones: listado paginado del usuario autenticado.
     */
    public function index(Request $request): Response
    {
        $usuario = $request->user();

        $notificaciones = Notificacion::query()
            ->where('user_id', $usuario->getKey())
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (Notificacion $n) => $this->transformar($n));

        return Inertia::render('Notificaciones/Index', [
            'notificaciones' => $notificaciones,
            'noLeidas' => $this->contarNoLeidas($request),
        ]);
    }

    /**
     * Endpoint JSON que alimenta la campana de notificaciones.
     */
    public function recientes(Request $request): JsonResponse
    {
        $usuario = $request->user();

        $items = Notificacion::query()
            ->where('user_id', $usuario->getKey())
            ->latest()
            ->limit(8)
            ->get()
            ->map(fn (Notificacion $n) => $this->transformar($n))
            ->values();

        return response()->json([
            'data' => [
                'items' => $items,
                'no_leidas' => $this->contarNoLeidas($request),
            ],
            'error' => null,
        ]);
    }

    /**
     * Marca una notificación como leída.
     */
    public function marcarLeida(Request $request, Notificacion $notificacion): RedirectResponse
    {
        $this->autorizar($request, $notificacion);

        if ($notificacion->leida_at === null) {
            $notificacion->forceFill(['leida_at' => now()])->save();
        }

        return back();
    }

    /**
     * Marca todas las notificaciones del usuario como leídas.
     */
    public function marcarTodasLeidas(Request $request): RedirectResponse
    {
        Notificacion::query()
            ->where('user_id', $request->user()->getKey())
            ->whereNull('leida_at')
            ->update(['leida_at' => now()]);

        return back();
    }

    /**
     * Elimina una notificación.
     */
    public function destroy(Request $request, Notificacion $notificacion): RedirectResponse
    {
        $this->autorizar($request, $notificacion);

        $notificacion->delete();

        return back();
    }

    /**
     * Asegura que la notificación pertenezca al usuario autenticado.
     */
    protected function autorizar(Request $request, Notificacion $notificacion): void
    {
        abort_unless($notificacion->user_id === $request->user()->getKey(), 403);
    }

    /**
     * Cuenta las notificaciones sin leer del usuario autenticado.
     */
    protected function contarNoLeidas(Request $request): int
    {
        return Notificacion::query()
            ->where('user_id', $request->user()->getKey())
            ->whereNull('leida_at')
            ->count();
    }

    /**
     * Normaliza una notificación para el front-end.
     *
     * @return array<string, mixed>
     */
    protected function transformar(Notificacion $notificacion): array
    {
        return [
            'id' => $notificacion->id,
            'tipo' => $notificacion->tipo,
            'titulo' => $notificacion->titulo,
            'mensaje' => $notificacion->mensaje,
            'enlace' => $notificacion->enlace,
            'leida' => $notificacion->leida_at !== null,
            'fecha' => $notificacion->created_at?->diffForHumans(),
            'fecha_exacta' => $notificacion->created_at?->format('d/m/Y H:i'),
        ];
    }
}
