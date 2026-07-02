<?php

namespace App\Providers;

use App\Services\CarritoService;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Fusiona el carrito de invitado con el del usuario al iniciar sesión,
        // para que nunca se pierdan los productos agregados antes de loguearse.
        Event::listen(Login::class, function (Login $event): void {
            app(CarritoService::class)->fusionarAlLogin(request(), $event->user);
        });
    }
}
