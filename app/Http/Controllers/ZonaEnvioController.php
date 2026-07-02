<?php

namespace App\Http\Controllers;

use App\Models\ZonaEnvio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ZonaEnvioController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Envios/Index', [
            'zonas' => ZonaEnvio::orderBy('nombre')->get()->map(fn (ZonaEnvio $zona) => [
                'id' => $zona->id,
                'nombre' => $zona->nombre,
                'provincias' => $zona->provincias ?? [],
                'costo' => (float) $zona->costo,
                'tiempo_estimado' => $zona->tiempo_estimado,
                'activo' => $zona->activo,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        ZonaEnvio::create($this->validated($request));

        return back()->with('success', 'Zona de envío creada correctamente.');
    }

    public function update(Request $request, ZonaEnvio $envio): RedirectResponse
    {
        $envio->update($this->validated($request));

        return back()->with('success', 'Zona de envío actualizada correctamente.');
    }

    public function destroy(ZonaEnvio $envio): RedirectResponse
    {
        $envio->delete();

        return back()->with('success', 'Zona de envío eliminada.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:120'],
            'provincias' => ['required', 'array', 'min:1'],
            'provincias.*' => ['string', 'max:80'],
            'costo' => ['required', 'numeric', 'min:0'],
            'tiempo_estimado' => ['nullable', 'string', 'max:80'],
            'activo' => ['boolean'],
        ]);

        return $data;
    }
}
