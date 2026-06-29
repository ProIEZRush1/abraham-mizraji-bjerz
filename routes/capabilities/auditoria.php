<?php

/**
 * Capacidad: Auditoría (Bitácora).
 *
 * Este archivo SOLO define rutas y se carga dentro del grupo
 * web + auth que abre CapabilitiesServiceProvider. No declara
 * middleware ni grupos adicionales.
 */

use App\Http\Controllers\AuditoriaController;
use Illuminate\Support\Facades\Route;

Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
