<?php

use App\Http\Controllers\Api\V1\ApiV1ExampleController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API REST — versión 1 (capacidad: API REST + Tokens)
|--------------------------------------------------------------------------
|
| Este archivo NO se carga por sí mismo. Lo carga routes/capabilities/api.php
| dentro de un grupo SIN estado:
|
|   Route::withoutMiddleware(['web', 'auth'])
|       ->prefix('api/v1')
|       ->middleware('auth:sanctum')
|       ->name('api.v1.')
|       ->group(base_path('routes/api.php'));
|
| Por eso aquí solo se declaran rutas "desnudas": ya heredan el prefijo
| /api/v1, el guard de tokens (auth:sanctum) y el prefijo de nombre api.v1.
| Autenticación: cabecera "Authorization: Bearer <token>".
|
*/

// GET /api/v1/user — comprobación rápida del token: devuelve el usuario.
Route::get('/user', fn (Request $request) => response()->json([
    'data' => $request->user(),
    'error' => null,
]))->name('user');

// Recurso REST de ejemplo: index, store, show, update, destroy.
Route::apiResource('example', ApiV1ExampleController::class);
