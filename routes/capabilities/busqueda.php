<?php

use App\Http\Controllers\BusquedaController;
use Illuminate\Support\Facades\Route;

// BÚSQUEDA GLOBAL — se ejecuta dentro del grupo web + auth abierto por
// CapabilitiesServiceProvider. Solo declara rutas; no abre grupos.
Route::get('/buscar', [BusquedaController::class, 'index'])->name('busqueda.index');
