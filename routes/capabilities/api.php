<?php

use App\Http\Controllers\ApiTokensController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Capacidad: API REST + Tokens
|--------------------------------------------------------------------------
|
| PARTE WEB (panel). Estas rutas se ejecutan dentro del grupo web + auth que
| abre CapabilitiesServiceProvider, por lo que se declaran "desnudas". Gestionan
| los tokens de acceso personal (Sanctum) desde la interfaz en español.
|
| La página vive en /api (no coincide con el patrón "api/*"); las mutaciones
| viven en /api-tokens para no quedar atrapadas por la regla de respuestas JSON
| de bootstrap/app.php (shouldRenderJsonWhen api/*), que rompería los redirects
| de Inertia en validación.
*/
Route::get('/api', [ApiTokensController::class, 'index'])->name('api.index');
Route::post('/api-tokens', [ApiTokensController::class, 'store'])->name('api.tokens.store');
Route::delete('/api-tokens/{tokenId}', [ApiTokensController::class, 'destroy'])
    ->whereNumber('tokenId')
    ->name('api.tokens.destroy');

/*
|--------------------------------------------------------------------------
| PARTE API (REST sin estado, /api/v1)
|--------------------------------------------------------------------------
|
| Los endpoints versionados se autentican con token Bearer de Sanctum, no con
| sesión. Por eso se desacoplan del grupo web + auth heredado con
| withoutMiddleware() y se aplica auth:sanctum. Esto es necesario porque
| bootstrap/app.php (archivo compartido, fuera de alcance) no registra
| routes/api.php; esta capacidad lo carga aquí de forma autónoma.
*/
Route::withoutMiddleware(['web', 'auth'])
    ->prefix('api/v1')
    ->middleware('auth:sanctum')
    ->name('api.v1.')
    ->group(base_path('routes/api.php'));
