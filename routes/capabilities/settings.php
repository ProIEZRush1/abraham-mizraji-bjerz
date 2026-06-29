<?php

use App\Http\Controllers\AjustesController;
use App\Services\AjustesSettingService;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Capacidad: AJUSTES Y BRANDING
|--------------------------------------------------------------------------
|
| Se ejecuta dentro del grupo web + auth abierto por CapabilitiesServiceProvider.
|
*/

// Expone la marca del negocio a TODA la UI vía Inertia (prop 'branding').
// Es un closure perezoso y tolerante a fallos: solo se evalúa al renderizar
// una respuesta Inertia y nunca lanza un 500 si la tabla aún no está migrada.
Inertia::share('branding', fn () => app(AjustesSettingService::class)->branding());

Route::get('/ajustes', [AjustesController::class, 'index'])->name('ajustes.index');
Route::put('/ajustes', [AjustesController::class, 'update'])->name('ajustes.update');
