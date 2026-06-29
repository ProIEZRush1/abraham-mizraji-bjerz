<?php

namespace App\Http\Controllers;

use App\Services\AjustesSettingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class AjustesController extends Controller
{
    public function __construct(private readonly AjustesSettingService $settings)
    {
    }

    /**
     * Página de Ajustes y Branding.
     */
    public function index(): Response
    {
        return Inertia::render('Ajustes/Edit', [
            'settings' => $this->settings->all(),
            'status' => session('status'),
        ]);
    }

    /**
     * Guarda los ajustes del negocio.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'business_name' => ['nullable', 'string', 'max:120'],
            'logo_url' => ['nullable', 'url', 'max:2048'],
            'brand_color_primary' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'brand_color_secondary' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:60'],
            'contact_address' => ['nullable', 'string', 'max:255'],
        ], [
            'logo_url.url' => 'La URL del logo no es válida.',
            'brand_color_primary.regex' => 'El color primario debe ser un hexadecimal, p. ej. #7c3aed.',
            'brand_color_secondary.regex' => 'El color secundario debe ser un hexadecimal, p. ej. #c026d3.',
            'contact_email.email' => 'El correo de contacto no es válido.',
        ]);

        $this->settings->setMany($validated);

        return Redirect::route('ajustes.index')->with('status', 'ajustes-guardados');
    }
}
