<?php

namespace App\Services;

use App\Models\Carrito;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * Resuelve el carrito activo (invitado o logueado) y fusiona el carrito de
 * invitado al carrito del usuario cuando inicia sesión, para que nunca se
 * pierdan los productos agregados antes de loguearse.
 */
class CarritoService
{
    public function actual(Request $request): Carrito
    {
        if ($request->user()) {
            return Carrito::firstOrCreate(['user_id' => $request->user()->id]);
        }

        $id = $request->session()->get('carrito_id');

        if ($id) {
            $carrito = Carrito::whereNull('user_id')->find($id);

            if ($carrito) {
                return $carrito;
            }
        }

        $carrito = Carrito::create(['session_id' => $request->session()->getId()]);
        $request->session()->put('carrito_id', $carrito->id);

        return $carrito;
    }

    /**
     * Cantidad de artículos en el carrito actual, sin crear uno nuevo si no
     * existe (para no generar carritos vacíos solo por mostrar el contador).
     */
    public function contarItems(Request $request): int
    {
        try {
            $carrito = $request->user()
                ? Carrito::where('user_id', $request->user()->id)->first()
                : Carrito::whereNull('user_id')->find($request->session()->get('carrito_id'));

            return $carrito ? (int) $carrito->items()->sum('cantidad') : 0;
        } catch (\Throwable) {
            return 0;
        }
    }

    public function fusionarAlLogin(Request $request, User $user): void
    {
        $id = $request->session()->get('carrito_id');

        if (! $id) {
            return;
        }

        $carritoInvitado = Carrito::whereNull('user_id')->find($id);

        if (! $carritoInvitado) {
            return;
        }

        $carritoUsuario = Carrito::firstOrCreate(['user_id' => $user->id]);

        foreach ($carritoInvitado->items as $item) {
            $existente = $carritoUsuario->items()
                ->where('producto_id', $item->producto_id)
                ->where('producto_variante_id', $item->producto_variante_id)
                ->first();

            if ($existente) {
                $existente->increment('cantidad', $item->cantidad);
                $item->delete();
            } else {
                $item->update(['carrito_id' => $carritoUsuario->id]);
            }
        }

        $carritoInvitado->delete();
        $request->session()->put('carrito_id', $carritoUsuario->id);
    }
}
