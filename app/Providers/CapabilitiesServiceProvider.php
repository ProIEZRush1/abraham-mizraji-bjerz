<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CapabilitiesServiceProvider extends ServiceProvider
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
     *
     * Auto-discovers every self-contained capability route file under
     * routes/capabilities/*.php and loads them inside a single
     * web + auth middleware group. Capability route files therefore
     * declare ONLY their routes and never re-declare the group.
     */
    public function boot(): void
    {
        $dir = base_path('routes/capabilities');

        if (! is_dir($dir)) {
            return;
        }

        $files = glob($dir.'/*.php') ?: [];
        sort($files);

        if (empty($files)) {
            return;
        }

        Route::middleware(['web', 'auth'])->group(function () use ($files) {
            foreach ($files as $file) {
                require $file;
            }
        });
    }
}
