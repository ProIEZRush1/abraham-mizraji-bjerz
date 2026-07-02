<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Carrito;
use App\Models\Producto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CarritoTest extends TestCase
{
    use RefreshDatabase;

    private function producto(): Producto
    {
        $categoria = Categoria::create(['nombre' => 'Accesorios', 'slug' => 'accesorios', 'activo' => true]);

        return Producto::create([
            'categoria_id' => $categoria->id,
            'nombre' => 'Cinturón de cuero',
            'slug' => 'cinturon-de-cuero',
            'precio' => 24900,
            'stock' => 20,
            'stock_minimo' => 5,
            'activo' => true,
        ]);
    }

    public function test_guest_can_add_a_product_to_the_cart_and_it_persists(): void
    {
        $producto = $this->producto();

        $response = $this->post('/carrito', ['producto_id' => $producto->id, 'cantidad' => 2]);
        $response->assertRedirect();

        $this->assertDatabaseHas('carrito_items', ['producto_id' => $producto->id, 'cantidad' => 2]);

        // Reload the cart page and confirm the item is still there.
        $carritoView = $this->get('/carrito');
        $carritoView->assertOk();
    }

    public function test_cart_merges_into_user_cart_on_login(): void
    {
        $producto = $this->producto();
        $user = User::factory()->create(['email_verified_at' => now()]);

        $this->post('/carrito', ['producto_id' => $producto->id, 'cantidad' => 1]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect();

        $carrito = Carrito::where('user_id', $user->id)->first();
        $this->assertNotNull($carrito);
        $this->assertSame(1, $carrito->items()->sum('cantidad'));
    }
}
