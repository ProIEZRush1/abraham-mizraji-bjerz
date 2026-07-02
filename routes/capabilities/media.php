<?php

use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

// Capacidad: Archivos / Media. Estas rutas corren dentro del grupo
// web + auth abierto por CapabilitiesServiceProvider.
Route::get('/archivos', [MediaController::class, 'index'])->name('media.index');
Route::post('/archivos', [MediaController::class, 'store'])->name('media.store');
Route::delete('/archivos/{mediaFile}', [MediaController::class, 'destroy'])->name('media.destroy');
