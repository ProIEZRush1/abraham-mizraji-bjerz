<?php

namespace App\Http\Controllers;

use App\Models\Direccion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DireccionController extends Controller
{
    public function index(Request $request): Response
    {
        return Inertia::render('MiCuenta/Direcciones', [
            'direcciones' => $request->user()->direcciones()->orderByDesc('predeterminada')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['user_id'] = $request->user()->id;

        if ($data['predeterminada'] ?? false) {
            $request->user()->direcciones()->update(['predeterminada' => false]);
        }

        $request->user()->direcciones()->create($data);

        return back()->with('success', 'Dirección guardada correctamente.');
    }

    public function update(Request $request, Direccion $direccion): RedirectResponse
    {
        $this->autorizar($request, $direccion);

        $data = $this->validated($request);

        if ($data['predeterminada'] ?? false) {
            $request->user()->direcciones()->where('id', '!=', $direccion->id)->update(['predeterminada' => false]);
        }

        $direccion->update($data);

        return back()->with('success', 'Dirección actualizada correctamente.');
    }

    public function destroy(Request $request, Direccion $direccion): RedirectResponse
    {
        $this->autorizar($request, $direccion);

        $direccion->delete();

        return back()->with('success', 'Dirección eliminada.');
    }

    private function autorizar(Request $request, Direccion $direccion): void
    {
        abort_unless($direccion->user_id === $request->user()->id, 403);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'etiqueta' => ['required', 'string', 'max:40'],
            'destinatario' => ['required', 'string', 'max:120'],
            'calle' => ['required', 'string', 'max:150'],
            'numero' => ['required', 'string', 'max:20'],
            'piso_depto' => ['nullable', 'string', 'max:40'],
            'ciudad' => ['required', 'string', 'max:100'],
            'provincia' => ['required', 'string', 'max:100'],
            'codigo_postal' => ['required', 'string', 'max:20'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'predeterminada' => ['boolean'],
        ]);
    }
}
