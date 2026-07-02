<?php

namespace Tests\Feature;

use App\Models\Cupon;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CuponTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_a_cupon_and_it_persists(): void
    {
        $admin = User::factory()->create(['email_verified_at' => now()]);
        $role = Role::firstOrCreate(['name' => 'admin'], ['label' => 'Administrador']);
        $admin->roles()->attach($role);

        $response = $this->actingAs($admin)->post('/cupones', [
            'codigo' => 'bienvenido10',
            'tipo' => 'porcentaje',
            'valor' => 10,
            'activo' => true,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('cupones', ['codigo' => 'BIENVENIDO10', 'valor' => 10]);
    }

    public function test_cupon_discount_calculation(): void
    {
        $cupon = Cupon::create(['codigo' => 'DESC20', 'tipo' => 'porcentaje', 'valor' => 20, 'activo' => true]);
        $this->assertTrue($cupon->esValidoPara(1000));
        $this->assertEquals(200, $cupon->calcularDescuento(1000));

        $fijo = Cupon::create(['codigo' => 'FIJO500', 'tipo' => 'monto_fijo', 'valor' => 500, 'activo' => true]);
        $this->assertEquals(500, $fijo->calcularDescuento(1000));

        $vencido = Cupon::create([
            'codigo' => 'VIEJO',
            'tipo' => 'porcentaje',
            'valor' => 10,
            'fecha_vencimiento' => now()->subDay()->toDateString(),
            'activo' => true,
        ]);
        $this->assertFalse($vencido->esValidoPara(1000));
    }
}
