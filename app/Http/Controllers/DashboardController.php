<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use App\Models\Cupon;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $inicioMes = Carbon::now()->startOfMonth();

        return Inertia::render('Dashboard', [
            'stats' => [
                'productos' => Producto::count(),
                'categorias' => Categoria::count(),
                'pedidos_mes' => Pedido::where('created_at', '>=', $inicioMes)->count(),
                'ingresos_mes' => (float) Pedido::where('created_at', '>=', $inicioMes)
                    ->whereIn('estado', ['pagado', 'en_preparacion', 'enviado', 'entregado'])
                    ->sum('total'),
                'clientes' => User::whereDoesntHave('roles', fn ($q) => $q->where('name', 'admin'))->count(),
                'pedidos_pendientes' => Pedido::where('estado', 'pendiente')->count(),
                'bajo_stock' => Producto::whereColumn('stock', '<=', 'stock_minimo')->count(),
                'cupones_activos' => Cupon::where('activo', true)->count(),
            ],
            'productosBajoStock' => Producto::whereColumn('stock', '<=', 'stock_minimo')
                ->orderBy('stock')
                ->take(5)
                ->get(['id', 'nombre', 'stock', 'stock_minimo']),
            'ultimosPedidos' => Pedido::with('user:id,name')
                ->orderByDesc('id')
                ->take(5)
                ->get()
                ->map(fn (Pedido $pedido) => [
                    'id' => $pedido->id,
                    'numero_pedido' => $pedido->numero_pedido,
                    'cliente' => $pedido->user?->name,
                    'estado' => $pedido->estado,
                    'total' => (float) $pedido->total,
                ]),
        ]);
    }
}
