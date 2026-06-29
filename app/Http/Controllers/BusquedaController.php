<?php

namespace App\Http\Controllers;

use App\Services\BusquedaService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BusquedaController extends Controller
{
    /**
     * Muestra la página de búsqueda global y, si hay término, sus resultados.
     */
    public function index(Request $request, BusquedaService $busqueda): Response
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $term = trim((string) ($validated['q'] ?? ''));

        $groups = $term === '' ? [] : $busqueda->search($term);

        $total = array_sum(array_map(fn (array $group) => (int) $group['count'], $groups));

        return Inertia::render('Busqueda/Index', [
            'q' => $term,
            'groups' => $groups,
            'total' => $total,
        ]);
    }
}
