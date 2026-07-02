<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Direccion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PedidoAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_marking_a_pedido_as_paid_decrements_stock_and_persists(): void
    {
        Mail::fake();

        $admin = User::factory()->create(['email_verified_at' => now()]);
        $role = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Administrador']);
        $admin->roles()->attach($role);

        $cliente = User::factory()->create(['email_verified_at' => now()]);
        $categoria = Categoria::create(['nombre' => 'Indumentaria', 'slug' => 'indumentaria', 'activo' => true]);
        $producto = Producto::create([
            'categoria_id' => $categoria->id,
            'nombre' => 'Buzo oversize',
            'slug' => 'buzo-oversize',
            'precio' => 38900,
            'stock' => 10,
            'stock_minimo' => 5,
            'activo' => true,
        ]);

        $direccion = Direccion::create([
            'user_id' => $cliente->id,
            'etiqueta' => 'Casa',
            'destinatario' => 'Cliente Demo',
            'calle' => 'Calle Falsa',
            'numero' => '123',
            'ciudad' => 'CABA',
            'provincia' => 'CABA',
            'codigo_postal' => 'C1000',
            'predeterminada' => true,
        ]);

        $pedido = Pedido::create([
            'numero_pedido' => 'PED-000001',
            'user_id' => $cliente->id,
            'direccion_id' => $direccion->id,
            'envio_destinatario' => $direccion->destinatario,
            'envio_calle' => $direccion->calle,
            'envio_numero' => $direccion->numero,
            'envio_ciudad' => $direccion->ciudad,
            'envio_provincia' => $direccion->provincia,
            'envio_codigo_postal' => $direccion->codigo_postal,
            'subtotal' => 38900 * 3,
            'total' => 38900 * 3,
            'estado' => 'pendiente',
        ]);

        $pedido->items()->create([
            'producto_id' => $producto->id,
            'nombre_producto' => $producto->nombre,
            'precio_unitario' => $producto->precio,
            'cantidad' => 3,
            'subtotal' => 38900 * 3,
        ]);

        $response = $this->actingAs($admin)->patch("/pedidos/{$pedido->id}/estado", ['estado' => 'pagado']);
        $response->assertRedirect();

        $pedido->refresh();
        $this->assertSame('pagado', $pedido->estado);
        $this->assertNotNull($pedido->pagado_at);

        $producto->refresh();
        $this->assertSame(7, $producto->stock);
    }
}
