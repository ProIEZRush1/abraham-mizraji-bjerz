<?php

namespace App\Services;

use App\Models\Pedido;
use App\Models\PedidoItem;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

/**
 * Reporte de ventas real, calculado a partir de los pedidos del negocio.
 *
 * Se consideran "vendidos" los pedidos en un estado que implica que el pago
 * se concretó (pagado, en_preparacion, enviado, entregado); los pendientes y
 * cancelados no suman a los ingresos. Degrada con elegancia si las tablas
 * aún no existen, para que el panel nunca arroje un error 500.
 */
class ReportesService
{
    private const ESTADOS_VENDIDOS = ['pagado', 'en_preparacion', 'enviado', 'entregado'];

    public function disponible(): bool
    {
        return Schema::hasTable('pedidos');
    }

    /**
     * Tarjetas de resumen mostradas en la parte superior del reporte.
     *
     * @return array<int, array<string, mixed>>
     */
    public function resumen(): array
    {
        $pedidosVendidos = 0;
        $ingresos = 0.0;
        $productosVendidos = 0;

        if ($this->disponible()) {
            $vendidos = Pedido::whereIn('estado', self::ESTADOS_VENDIDOS);
            $pedidosVendidos = (clone $vendidos)->count();
            $ingresos = (float) (clone $vendidos)->sum('total');
            $productosVendidos = (int) PedidoItem::whereHas(
                'pedido',
                fn ($q) => $q->whereIn('estado', self::ESTADOS_VENDIDOS)
            )->sum('cantidad');
        }

        $ticketPromedio = $pedidosVendidos > 0 ? $ingresos / $pedidosVendidos : 0.0;

        return [
            [
                'label' => 'Ingresos totales',
                'valor' => '$'.number_format($ingresos, 2, '.', ','),
                'hint' => 'Pedidos pagados o en curso',
                'gradient' => 'from-[#92400e] to-[#b45309]',
            ],
            [
                'label' => 'Pedidos vendidos',
                'valor' => number_format($pedidosVendidos, 0, '.', ','),
                'hint' => 'No incluye pendientes ni cancelados',
                'gradient' => 'from-[#78350f] to-[#d97706]',
            ],
            [
                'label' => 'Ticket promedio',
                'valor' => '$'.number_format($ticketPromedio, 2, '.', ','),
                'hint' => 'Valor promedio por pedido',
                'gradient' => 'from-[#92400e] to-[#d97706]',
            ],
            [
                'label' => 'Productos vendidos',
                'valor' => number_format($productosVendidos, 0, '.', ','),
                'hint' => 'Unidades totales despachadas',
                'gradient' => 'from-[#d97706] to-[#f59e0b]',
            ],
        ];
    }

    /**
     * Serie diaria de ingresos para los últimos N días (gráfica de barras).
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
            $porFecha = Pedido::query()
                ->whereIn('estado', self::ESTADOS_VENDIDOS)
                ->whereDate('created_at', '>=', $inicio->toDateString())
                ->selectRaw('DATE(created_at) as fecha, SUM(total) as total')
                ->groupBy('fecha')
                ->pluck('total', 'fecha');
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
     * Ingresos agrupados por categoría de producto.
     *
     * @return array<int, array<string, mixed>>
     */
    public function porCategoria(): array
    {
        if (! $this->disponible()) {
            return [];
        }

        return PedidoItem::query()
            ->whereHas('pedido', fn ($q) => $q->whereIn('estado', self::ESTADOS_VENDIDOS))
            ->join('productos', 'productos.id', '=', 'pedido_items.producto_id')
            ->join('categorias', 'categorias.id', '=', 'productos.categoria_id')
            ->selectRaw('categorias.nombre as categoria, COUNT(*) as registros, SUM(pedido_items.subtotal) as total')
            ->groupBy('categorias.nombre')
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
     * Los productos más vendidos por unidades, entre los pedidos vendidos.
     *
     * @return array<int, array<string, mixed>>
     */
    public function productosMasVendidos(int $limite = 5): array
    {
        if (! $this->disponible()) {
            return [];
        }

        return PedidoItem::query()
            ->whereHas('pedido', fn ($q) => $q->whereIn('estado', self::ESTADOS_VENDIDOS))
            ->selectRaw('nombre_producto, SUM(cantidad) as unidades, SUM(subtotal) as ingresos')
            ->groupBy('nombre_producto')
            ->orderByDesc('unidades')
            ->take($limite)
            ->get()
            ->map(fn ($fila) => [
                'nombre' => $fila->nombre_producto,
                'unidades' => (int) $fila->unidades,
                'ingresos' => round((float) $fila->ingresos, 2),
            ])
            ->all();
    }

    /**
     * Un renglón por pedido vendido, usado por las exportaciones (CSV / PDF).
     *
     * @return Collection<int, object>
     */
    public function registros(): Collection
    {
        if (! $this->disponible()) {
            return collect();
        }

        return Pedido::whereIn('estado', self::ESTADOS_VENDIDOS)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn (Pedido $pedido) => (object) [
                'id' => $pedido->id,
                'categoria' => 'Pedido',
                'etiqueta' => $pedido->numero_pedido,
                'valor' => (float) $pedido->total,
                'ocurrido_el' => $pedido->created_at,
            ]);
    }
}
