<?php

namespace App\Services;

use App\Models\ReporteMetrica;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Agrega métricas genéricas del negocio para la sección de Reportes.
 *
 * El servicio es autocontenido: lee únicamente la tabla `reportes_metricas`
 * propia de esta capacidad y degrada con elegancia (devuelve ceros / series
 * vacías) cuando la migración aún no se ha ejecutado, de modo que el panel
 * nunca arroje un error 500.
 */
class ReportesService
{
    /**
     * Indica si el almacén de métricas está disponible.
     */
    public function disponible(): bool
    {
        return Schema::hasTable('reportes_metricas');
    }

    /**
     * Tarjetas de resumen mostradas en la parte superior del reporte.
     *
     * @return array<int, array<string, mixed>>
     */
    public function resumen(): array
    {
        $total = 0;
        $suma = 0.0;
        $categorias = 0;

        if ($this->disponible()) {
            $total = ReporteMetrica::query()->count();
            $suma = (float) ReporteMetrica::query()->sum('valor');
            $categorias = ReporteMetrica::query()->distinct('categoria')->count('categoria');
        }

        $promedio = $total > 0 ? $suma / $total : 0.0;

        return [
            [
                'label' => 'Registros totales',
                'valor' => number_format($total, 0, '.', ','),
                'hint' => 'Métricas capturadas',
                'gradient' => 'from-[#7c3aed] to-[#a855f7]',
            ],
            [
                'label' => 'Valor acumulado',
                'valor' => '$'.number_format($suma, 2, '.', ','),
                'hint' => 'Suma de todas las métricas',
                'gradient' => 'from-[#a21caf] to-[#c026d3]',
            ],
            [
                'label' => 'Categorías',
                'valor' => number_format($categorias, 0, '.', ','),
                'hint' => 'Áreas medidas',
                'gradient' => 'from-[#7c3aed] to-[#c026d3]',
            ],
            [
                'label' => 'Promedio por registro',
                'valor' => '$'.number_format($promedio, 2, '.', ','),
                'hint' => 'Valor medio',
                'gradient' => 'from-[#c026d3] to-[#db2777]',
            ],
        ];
    }

    /**
     * Serie diaria de valores para los últimos N días (gráfica de barras).
     *
     * @return array<int, array<string, mixed>>
     */
    public function serieDiaria(int $dias = 14): array
    {
        $dias = max(1, min($dias, 90));
        $hoy = CarbonImmutable::today();
        $inicio = $hoy->subDays($dias - 1);

        $porFecha = collect();

        if ($this->disponible()) {
            $porFecha = ReporteMetrica::query()
                ->where('ocurrido_el', '>=', $inicio->toDateString())
                ->selectRaw('ocurrido_el, SUM(valor) as total')
                ->groupBy('ocurrido_el')
                ->pluck('total', 'ocurrido_el');
        }

        $serie = [];
        for ($i = 0; $i < $dias; $i++) {
            $fecha = $inicio->addDays($i);
            $clave = $fecha->toDateString();
            $serie[] = [
                'fecha' => $clave,
                'etiqueta' => $fecha->translatedFormat('d M'),
                'total' => round((float) ($porFecha[$clave] ?? 0), 2),
            ];
        }

        return $serie;
    }

    /**
     * Totales agrupados por categoría (desglose).
     *
     * @return array<int, array<string, mixed>>
     */
    public function porCategoria(): array
    {
        if (! $this->disponible()) {
            return [];
        }

        return ReporteMetrica::query()
            ->selectRaw('categoria, COUNT(*) as registros, SUM(valor) as total')
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($fila) => [
                'categoria' => $fila->categoria,
                'registros' => (int) $fila->registros,
                'total' => round((float) $fila->total, 2),
            ])
            ->all();
    }

    /**
     * Registros individuales usados por las exportaciones (CSV / PDF).
     *
     * @return Collection<int, ReporteMetrica>
     */
    public function registros(): Collection
    {
        if (! $this->disponible()) {
            return collect();
        }

        return ReporteMetrica::query()
            ->orderByDesc('ocurrido_el')
            ->orderByDesc('id')
            ->get();
    }
}
