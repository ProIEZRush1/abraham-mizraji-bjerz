<?php

use App\Http\Controllers\ImporterController;
use Illuminate\Support\Facades\Route;

// Capacidad: Importación CSV.
// Estas rutas corren dentro del grupo web + auth abierto por
// CapabilitiesServiceProvider. No envolver en Route::middleware()->group().
Route::get('/importar', [ImporterController::class, 'index'])->name('importer.index');
Route::post('/importar/analizar', [ImporterController::class, 'analyze'])->name('importer.analyze');
Route::post('/importar/ejecutar', [ImporterController::class, 'import'])->name('importer.import');
