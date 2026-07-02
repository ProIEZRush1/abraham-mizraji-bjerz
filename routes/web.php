<?php

use App\Http\Controllers\CarritoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CuponController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DireccionController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\MisPedidosController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TiendaController;
use App\Http\Controllers\ZonaEnvioController;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Tienda pública (sin autenticación)
|--------------------------------------------------------------------------
| Esta es una tienda en línea de cara al público: "/" es la landing real de
| Abraham Mizraji, nunca un redirect al login/panel.
*/
Route::get('/', [TiendaController::class, 'home'])->name('home');
Route::get('/tienda', [TiendaController::class, 'index'])->name('tienda.index');
Route::get('/tienda/{producto:slug}', [TiendaController::class, 'show'])->name('tienda.show');

Route::get('/carrito', [CarritoController::class, 'index'])->name('carrito.index');
Route::post('/carrito', [CarritoController::class, 'store'])->name('carrito.store');
Route::patch('/carrito/{item}', [CarritoController::class, 'update'])->name('carrito.update');
Route::delete('/carrito/{item}', [CarritoController::class, 'destroy'])->name('carrito.destroy');

// Webhook de Mercado Pago: público, sin sesión ni verificación CSRF.
Route::post('/mercadopago/webhook', [MercadoPagoController::class, 'webhook'])
    ->withoutMiddleware([ValidateCsrfToken::class])
    ->name('mercadopago.webhook');

// Lightweight health probe the deploy pipeline hits to verify the LIVE app + database are up,
// migrations ran and the admin was seeded (users >= 1). Public on purpose.
Route::get('/health', function () {
    try {
        return response()->json(['ok' => true, 'users' => \App\Models\User::count()]);
    } catch (\Throwable $e) {
        return response()->json(['ok' => false, 'error' => 'db'], 503);
    }
});

/*
|--------------------------------------------------------------------------
| Cuenta del cliente y checkout (requieren sesión)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'store'])
        ->middleware('trial.lock')
        ->name('checkout.store');
    Route::get('/checkout/exito/{pedido}', [CheckoutController::class, 'exito'])->name('checkout.exito');

    Route::get('/mis-pedidos', [MisPedidosController::class, 'index'])->name('mis-pedidos.index');
    Route::get('/mis-pedidos/{pedido}', [MisPedidosController::class, 'show'])->name('mis-pedidos.show');

    Route::get('/mi-cuenta/direcciones', [DireccionController::class, 'index'])->name('direcciones.index');
    Route::post('/mi-cuenta/direcciones', [DireccionController::class, 'store'])->name('direcciones.store');
    Route::put('/mi-cuenta/direcciones/{direccion}', [DireccionController::class, 'update'])->name('direcciones.update');
    Route::delete('/mi-cuenta/direcciones/{direccion}', [DireccionController::class, 'destroy'])->name('direcciones.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Panel de administración (auth + rol admin)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::resource('productos', ProductoController::class)->except(['show']);
    Route::post('/productos/imagenes', [ProductoController::class, 'subirImagen'])->name('productos.imagenes');

    Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index');
    Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
    Route::put('/categorias/{categoria}', [CategoriaController::class, 'update'])->name('categorias.update');
    Route::delete('/categorias/{categoria}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');

    Route::get('/pedidos', [PedidoController::class, 'index'])->name('pedidos.index');
    Route::get('/pedidos/{pedido}', [PedidoController::class, 'show'])->name('pedidos.show');
    Route::patch('/pedidos/{pedido}/estado', [PedidoController::class, 'actualizarEstado'])->name('pedidos.estado');

    Route::get('/cupones', [CuponController::class, 'index'])->name('cupones.index');
    Route::post('/cupones', [CuponController::class, 'store'])->name('cupones.store');
    Route::put('/cupones/{cupon}', [CuponController::class, 'update'])->name('cupones.update');
    Route::delete('/cupones/{cupon}', [CuponController::class, 'destroy'])->name('cupones.destroy');

    Route::get('/envios', [ZonaEnvioController::class, 'index'])->name('envios.index');
    Route::post('/envios', [ZonaEnvioController::class, 'store'])->name('envios.store');
    Route::put('/envios/{envio}', [ZonaEnvioController::class, 'update'])->name('envios.update');
    Route::delete('/envios/{envio}', [ZonaEnvioController::class, 'destroy'])->name('envios.destroy');
});

require __DIR__.'/auth.php';
