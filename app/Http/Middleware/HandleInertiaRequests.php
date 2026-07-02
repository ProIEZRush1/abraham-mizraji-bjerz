<?php

namespace App\Http\Middleware;

use App\Services\CarritoService;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'capabilities' => config('capabilities.nav', []),
            'auth' => [
                'user' => $request->user(),
                'isAdmin' => (bool) $request->user()?->hasRole('admin'),
            ],
            'trialLocked' => trial_locked(),
            'carritoCount' => app(CarritoService::class)->contarItems($request),
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
