<?php

namespace App\Http\Controllers;

use App\Models\Cupon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CuponController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Cupones/Index', [
            'cupones' => Cupon::orderByDesc('id')->get()->map(fn (Cupon $cupon) => [
                'id' => $cupon->id,
                'codigo' => $cupon->codigo,
                'tipo' => $cupon->tipo,
                'valor' => (float) $cupon->valor,
                'monto_minimo' => $cupon->monto_minimo !== null ? (float) $cupon->monto_minimo : null,
                'fecha_inicio' => $cupon->fecha_inicio?->toDateString(),
                'fecha_vencimiento' => $cupon->fecha_vencimiento?->toDateString(),
                'uso_maximo' => $cupon->uso_maximo,
                'usos_actuales' => $cupon->usos_actuales,
                'activo' => $cupon->activo,
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['codigo'] = strtoupper($data['codigo']);

        Cupon::create($data);

        return back()->with('success', 'Cupón creado correctamente.');
    }

    public function update(Request $request, Cupon $cupon): RedirectResponse
    {
        $data = $this->validated($request, $cupon->id);
        $data['codigo'] = strtoupper($data['codigo']);

        $cupon->update($data);

        return back()->with('success', 'Cupón actualizado correctamente.');
    }

    public function destroy(Cupon $cupon): RedirectResponse
    {
        $cupon->delete();

        return back()->with('success', 'Cupón eliminado.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'codigo' => ['required', 'string', 'max:40', 'unique:cupones,codigo,'.($ignoreId ?? 'NULL').',id'],
            'tipo' => ['required', 'in:porcentaje,monto_fijo'],
            'valor' => ['required', 'numeric', 'min:0'],
            'monto_minimo' => ['nullable', 'numeric', 'min:0'],
            'fecha_inicio' => ['nullable', 'date'],
            'fecha_vencimiento' => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'uso_maximo' => ['nullable', 'integer', 'min:1'],
            'activo' => ['boolean'],
        ]);
    }
}
