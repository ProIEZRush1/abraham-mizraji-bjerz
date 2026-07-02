<?php

use App\Http\Controllers\PagosPaymentController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

/*
| Capacidad: Pagos (Stripe).
| Este archivo corre DENTRO del grupo web + auth abierto por
| CapabilitiesServiceProvider, así que solo declara rutas.
*/

Route::get('/pagos', [PagosPaymentController::class, 'index'])->name('pagos.index');
Route::post('/pagos', [PagosPaymentController::class, 'store'])->name('pagos.store');

// El webhook de Stripe es público: sin autenticación y sin verificación CSRF.
Route::post('/pagos/webhook', [PagosPaymentController::class, 'webhook'])
    ->withoutMiddleware(['auth', ValidateCsrfToken::class])
    ->name('pagos.webhook');
