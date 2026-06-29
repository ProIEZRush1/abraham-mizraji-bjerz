<?php

use App\Http\Controllers\RolesController;
use App\Http\Controllers\UsuariosController;
use App\Http\Middleware\EnsureUserHasRole;
use Illuminate\Support\Facades\Route;

/*
 * Capacidad: Roles y permisos + gestión de usuarios.
 *
 * Estas rutas ya corren dentro del grupo web + auth abierto por
 * CapabilitiesServiceProvider. Solo el rol "admin" puede acceder, por lo que
 * se aplica el middleware de rol por ruta (sin reabrir grupos).
 */
$admin = EnsureUserHasRole::class.':admin';

Route::get('/usuarios', [RolesController::class, 'index'])
    ->middleware($admin)
    ->name('roles.index');

Route::post('/roles', [RolesController::class, 'store'])
    ->middleware($admin)
    ->name('roles.store');

Route::put('/roles/{role}', [RolesController::class, 'update'])
    ->middleware($admin)
    ->name('roles.update');

Route::delete('/roles/{role}', [RolesController::class, 'destroy'])
    ->middleware($admin)
    ->name('roles.destroy');

Route::post('/usuarios', [UsuariosController::class, 'store'])
    ->middleware($admin)
    ->name('roles.users.store');

Route::put('/usuarios/{user}', [UsuariosController::class, 'update'])
    ->middleware($admin)
    ->name('roles.users.update');

Route::delete('/usuarios/{user}', [UsuariosController::class, 'destroy'])
    ->middleware($admin)
    ->name('roles.users.destroy');
