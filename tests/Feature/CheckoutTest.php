<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\User;
use App\Models\ZonaEnvio;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function producto(): Producto
    {
        $categoria = Categoria::create(['nombre' => 'Belleza', 'slug' => 'belleza', 'activo' => true]);

        return Producto::create([
            'categoria_id' => $categoria->id,
            'nombre' => 'Perfume floral',
            'slug' => 'perfume-floral',
            'precio' => 41900,
            'stock' => 10,
            'stock_minimo' => 5,
            'activo' => true,
        ]);
    }

    public function test_checkout_is_blocked_while_trial_is_locked(): void
    {
        config(['trial.locked' => true]);
        Mail::fake();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $producto = $this->producto();
        $zona = ZonaEnvio::create(['nombre' => 'CABA', 'provincias' => ['CABA'], 'costo' => 1500, 'activo' => true]);

        $this->actingAs($user)->post('/carrito', ['producto_id' => $producto->id, 'cantidad' => 1]);

        $response = $this->actingAs($user)->withHeaders(['X-Inertia' => 'true'])->post('/checkout', [
            'zona_envio_id' => $zona->id,
            'direccion_nueva' => [
                'destinatario' => 'Test User',
                'calle' => 'Calle Falsa',
                'numero' => '123',
                'ciudad' => 'CABA',
                'provincia' => 'CABA',
                'codigo_postal' => 'C1000',
            ],
        ]);

        $response->assertSessionHas('trial_locked', true);
        $this->assertDatabaseCount('pedidos', 0);
    }

    public function test_checkout_creates_a_pedido_when_trial_is_unlocked(): void
    {
        config(['trial.locked' => false]);
        Mail::fake();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $producto = $this->producto();
        $zona = ZonaEnvio::create(['nombre' => 'CABA', 'provincias' => ['CABA'], 'costo' => 1500, 'activo' => true]);

        $this->actingAs($user)->post('/carrito', ['producto_id' => $producto->id, 'cantidad' => 2]);

        $response = $this->actingAs($user)->post('/checkout', [
            'zona_envio_id' => $zona->id,
            'direccion_nueva' => [
                'destinatario' => 'Test User',
                'calle' => 'Calle Falsa',
                'numero' => '123',
                'ciudad' => 'CABA',
                'provincia' => 'CABA',
                'codigo_postal' => 'C1000',
            ],
        ]);

        $pedido = Pedido::first();
        $this->assertNotNull($pedido);
        $response->assertRedirect(route('checkout.exito', $pedido));

        $this->assertEquals(2, $pedido->items()->sum('cantidad'));
        $this->assertEquals(41900 * 2 + 1500, (float) $pedido->total);

        // El carrito debe quedar vacío tras confirmar el pedido.
        $this->assertDatabaseCount('carrito_items', 0);
    }
}
