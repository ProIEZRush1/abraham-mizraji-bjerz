<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restringe el acceso a una ruta a usuarios que tengan al menos uno de los
 * roles indicados. Uso en una ruta:
 *
 *   ->middleware(EnsureUserHasRole::class.':admin')
 *   ->middleware(EnsureUserHasRole::class.':admin,staff')
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! method_exists($user, 'hasAnyRole') || ! $user->hasAnyRole($roles)) {
            abort(403, 'No tienes permiso para acceder a esta sección.');
        }

        return $next($request);
    }
}
