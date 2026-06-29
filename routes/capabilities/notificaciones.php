<?php

use App\Http\Controllers\NotificacionesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Capacidad: Notificaciones
|--------------------------------------------------------------------------
|
| Estas rutas se cargan dentro del grupo web + auth abierto por
| CapabilitiesServiceProvider. No declarar middleware ni grupos aquí.
|
*/

Route::get('/notificaciones', [NotificacionesController::class, 'index'])
    ->name('notificaciones.index');

Route::get('/notificaciones/recientes', [NotificacionesController::class, 'recientes'])
    ->name('notificaciones.recientes');

Route::post('/notificaciones/leer-todas', [NotificacionesController::class, 'marcarTodasLeidas'])
    ->name('notificaciones.leer-todas');

Route::post('/notificaciones/{notificacion}/leer', [NotificacionesController::class, 'marcarLeida'])
    ->name('notificaciones.leer');

Route::delete('/notificaciones/{notificacion}', [NotificacionesController::class, 'destroy'])
    ->name('notificaciones.destroy');
