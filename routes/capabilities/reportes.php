<?php

use App\Http\Controllers\ReportesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Capacidad: Reportes
|--------------------------------------------------------------------------
|
| Estas rutas se cargan dentro del grupo web + auth abierto por
| CapabilitiesServiceProvider. No declarar middleware ni grupos aquí.
|
*/

Route::get('/reportes', [ReportesController::class, 'index'])->name('reportes.index');
Route::get('/reportes/exportar/csv', [ReportesController::class, 'exportCsv'])->name('reportes.export.csv');
Route::get('/reportes/exportar/pdf', [ReportesController::class, 'exportPdf'])->name('reportes.export.pdf');
