<?php

namespace Tests\Feature;

use App\Models\Categoria;
use App\Models\Producto;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductoCatalogoTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $role = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Administrador']);
        $admin->roles()->attach($role);

        return $admin;
    }

    public function test_admin_can_create_a_producto_and_it_persists(): void
    {
        $admin = $this->admin();
        $categoria = Categoria::create(['nombre' => 'Indumentaria', 'slug' => 'indumentaria', 'activo' => true]);

        $response = $this->actingAs($admin)->post('/productos', [
            'categoria_id' => $categoria->id,
            'nombre' => 'Camisa de lino',
            'descripcion' => 'Camisa fresca de verano',
            'precio' => 45900,
            'stock' => 10,
            'stock_minimo' => 5,
            'activo' => true,
            'destacado' => false,
        ]);

        $response->assertRedirect(route('productos.index'));

        $producto = Producto::where('nombre', 'Camisa de lino')->first();
        $this->assertNotNull($producto);
        $this->assertSame($categoria->id, $producto->categoria_id);
        $this->assertEquals(45900, $producto->precio);

        // Se recarga desde la BD y confirma que persiste tras el request.
        $this->assertDatabaseHas('productos', ['nombre' => 'Camisa de lino', 'stock' => 10]);
    }

    public function test_non_admin_cannot_manage_productos(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $response = $this->actingAs($user)->get('/productos');

        $response->assertForbidden();
    }

    public function test_producto_shows_in_public_storefront(): void
    {
        $categoria = Categoria::create(['nombre' => 'Calzado', 'slug' => 'calzado', 'activo' => true]);
        Producto::create([
            'categoria_id' => $categoria->id,
            'nombre' => 'Zapato de cuero',
            'slug' => 'zapato-de-cuero',
            'precio' => 78900,
            'stock' => 5,
            'stock_minimo' => 5,
            'activo' => true,
        ]);

        $response = $this->get('/tienda/zapato-de-cuero');

        $response->assertOk();
    }
}
