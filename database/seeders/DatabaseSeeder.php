<?php

namespace Database\Seeders;

use App\Models\Categoria;
use App\Models\Cupon;
use App\Models\Direccion;
use App\Models\Pedido;
use App\Models\Producto;
use App\Models\Role;
use App\Models\User;
use App\Models\ZonaEnvio;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(
            ['name' => 'admin'],
            ['label' => 'Administrador', 'description' => 'Acceso total al panel y a la gestión de usuarios.'],
        );

        // Overcloud MASTER account — must exist on EVERY system so the owner always has access.
        // Idempotent (updateOrCreate keeps the password current). Never remove. The User 'hashed'
        // cast hashes the plain password automatically.
        $superAdmin = User::updateOrCreate(
            ['email' => 'edumaucherni@gmail.com'],
            ['name' => 'Eduardo', 'password' => 'Eduardo2006!', 'email_verified_at' => now()],
        );
        $superAdmin->roles()->syncWithoutDetaching([$adminRole->id]);

        // Admin del negocio.
        $admin = User::updateOrCreate(
            ['email' => 'abraham-mizraji@overcloud.us'],
            ['name' => 'Abraham Mizraji', 'password' => Hash::make('9oe5rBGXRooT'), 'email_verified_at' => now()],
        );
        $admin->roles()->syncWithoutDetaching([$adminRole->id]);

        // Cliente de ejemplo (para poblar pedidos e historial de compras).
        $cliente = User::updateOrCreate(
            ['email' => 'cliente@example.com'],
            ['name' => 'Julieta Fernández', 'password' => Hash::make('cliente1234'), 'email_verified_at' => now()],
        );

        $direccion = Direccion::updateOrCreate(
            ['user_id' => $cliente->id, 'etiqueta' => 'Casa'],
            [
                'destinatario' => 'Julieta Fernández',
                'calle' => 'Av. Corrientes',
                'numero' => '2530',
                'piso_depto' => '4to B',
                'ciudad' => 'Ciudad Autónoma de Buenos Aires',
                'provincia' => 'CABA',
                'codigo_postal' => 'C1046',
                'telefono' => '+54 11 4444-2020',
                'predeterminada' => true,
            ],
        );

        // Categorías del giro (boutique de indumentaria y accesorios).
        $categorias = [
            ['nombre' => 'Indumentaria', 'descripcion' => 'Prendas para todos los días y ocasiones especiales.'],
            ['nombre' => 'Calzado', 'descripcion' => 'Zapatos, zapatillas y botas.'],
            ['nombre' => 'Accesorios', 'descripcion' => 'Cinturones, bijou y complementos.'],
            ['nombre' => 'Bolsos y carteras', 'descripcion' => 'Carteras, mochilas y bolsos de mano.'],
            ['nombre' => 'Belleza', 'descripcion' => 'Cuidado personal y fragancias.'],
        ];

        $categoriaModelos = [];

        foreach ($categorias as $categoria) {
            $categoriaModelos[$categoria['nombre']] = Categoria::updateOrCreate(
                ['slug' => Str::slug($categoria['nombre'])],
                [
                    'nombre' => $categoria['nombre'],
                    'descripcion' => $categoria['descripcion'],
                    'activo' => true,
                ],
            );
        }

        // Productos de ejemplo, con variantes de talle donde aplica.
        $productos = [
            ['nombre' => 'Camisa de lino Amalfi', 'categoria' => 'Indumentaria', 'precio' => 45900, 'precio_oferta' => null, 'stock' => 24, 'destacado' => true, 'talles' => ['S', 'M', 'L', 'XL']],
            ['nombre' => 'Vestido midi Florencia', 'categoria' => 'Indumentaria', 'precio' => 62900, 'precio_oferta' => 52900, 'stock' => 14, 'destacado' => true, 'talles' => ['S', 'M', 'L']],
            ['nombre' => 'Pantalón sastrero clásico', 'categoria' => 'Indumentaria', 'precio' => 51900, 'precio_oferta' => null, 'stock' => 3, 'destacado' => false, 'talles' => ['38', '40', '42', '44']],
            ['nombre' => 'Buzo oversize algodón', 'categoria' => 'Indumentaria', 'precio' => 38900, 'precio_oferta' => null, 'stock' => 30, 'destacado' => false, 'talles' => ['S', 'M', 'L']],
            ['nombre' => 'Zapato de cuero Berlín', 'categoria' => 'Calzado', 'precio' => 78900, 'precio_oferta' => null, 'stock' => 10, 'destacado' => true, 'talles' => ['38', '39', '40', '41', '42']],
            ['nombre' => 'Zapatillas urbanas Nolita', 'categoria' => 'Calzado', 'precio' => 69900, 'precio_oferta' => 59900, 'stock' => 4, 'destacado' => true, 'talles' => ['37', '38', '39', '40']],
            ['nombre' => 'Botas cortas texanas', 'categoria' => 'Calzado', 'precio' => 89900, 'precio_oferta' => null, 'stock' => 8, 'destacado' => false, 'talles' => ['36', '37', '38', '39']],
            ['nombre' => 'Cinturón de cuero reversible', 'categoria' => 'Accesorios', 'precio' => 24900, 'precio_oferta' => null, 'stock' => 20, 'destacado' => false, 'talles' => []],
            ['nombre' => 'Collar bijou dorado', 'categoria' => 'Accesorios', 'precio' => 15900, 'precio_oferta' => null, 'stock' => 2, 'destacado' => false, 'talles' => []],
            ['nombre' => 'Anteojos de sol Positano', 'categoria' => 'Accesorios', 'precio' => 32900, 'precio_oferta' => 27900, 'stock' => 16, 'destacado' => true, 'talles' => []],
            ['nombre' => 'Cartera de mano Capri', 'categoria' => 'Bolsos y carteras', 'precio' => 58900, 'precio_oferta' => null, 'stock' => 12, 'destacado' => true, 'talles' => []],
            ['nombre' => 'Mochila urbana impermeable', 'categoria' => 'Bolsos y carteras', 'precio' => 47900, 'precio_oferta' => null, 'stock' => 18, 'destacado' => false, 'talles' => []],
            ['nombre' => 'Perfume floral Essenza', 'categoria' => 'Belleza', 'precio' => 41900, 'precio_oferta' => null, 'stock' => 22, 'destacado' => false, 'talles' => []],
            ['nombre' => 'Set de skincare esencial', 'categoria' => 'Belleza', 'precio' => 36900, 'precio_oferta' => 29900, 'stock' => 1, 'destacado' => false, 'talles' => []],
        ];

        $productoModelos = [];

        foreach ($productos as $producto) {
            $modelo = Producto::updateOrCreate(
                ['slug' => Str::slug($producto['nombre'])],
                [
                    'categoria_id' => $categoriaModelos[$producto['categoria']]->id,
                    'nombre' => $producto['nombre'],
                    'descripcion' => "Producto de la colección {$producto['categoria']} de Abraham Mizraji, seleccionado por su calidad y diseño.",
                    'precio' => $producto['precio'],
                    'precio_oferta' => $producto['precio_oferta'],
                    'sku' => strtoupper(Str::slug($producto['nombre'], '')),
                    'stock' => $producto['talles'] ? 0 : $producto['stock'],
                    'stock_minimo' => 5,
                    'activo' => true,
                    'destacado' => $producto['destacado'],
                ],
            );

            if ($producto['talles']) {
                $stockRestante = $producto['stock'];
                $porTalle = (int) ceil($stockRestante / count($producto['talles']));

                foreach ($producto['talles'] as $talle) {
                    $modelo->variantes()->updateOrCreate(
                        ['nombre' => "Talle {$talle}"],
                        ['stock' => min($porTalle, $stockRestante), 'precio_extra' => 0, 'activo' => true],
                    );
                    $stockRestante = max(0, $stockRestante - $porTalle);
                }
            }

            $productoModelos[$producto['nombre']] = $modelo;
        }

        // Zonas de envío.
        $zonaCaba = ZonaEnvio::updateOrCreate(
            ['nombre' => 'CABA y GBA'],
            ['provincias' => ['CABA', 'Buenos Aires'], 'costo' => 1500, 'tiempo_estimado' => '1-2 días hábiles', 'activo' => true],
        );

        ZonaEnvio::updateOrCreate(
            ['nombre' => 'Interior del país'],
            [
                'provincias' => [
                    'Catamarca', 'Chaco', 'Chubut', 'Córdoba', 'Corrientes', 'Entre Ríos', 'Formosa',
                    'Jujuy', 'La Pampa', 'La Rioja', 'Mendoza', 'Misiones', 'Neuquén', 'Río Negro',
                    'Salta', 'San Juan', 'San Luis', 'Santa Cruz', 'Santa Fe', 'Santiago del Estero',
                    'Tierra del Fuego', 'Tucumán',
                ],
                'costo' => 3500,
                'tiempo_estimado' => '4-7 días hábiles',
                'activo' => true,
            ],
        );

        // Cupones de descuento.
        Cupon::updateOrCreate(
            ['codigo' => 'BIENVENIDO10'],
            ['tipo' => 'porcentaje', 'valor' => 10, 'monto_minimo' => null, 'activo' => true],
        );

        Cupon::updateOrCreate(
            ['codigo' => 'VERANO2000'],
            [
                'tipo' => 'monto_fijo',
                'valor' => 2000,
                'monto_minimo' => 30000,
                'fecha_vencimiento' => now()->addMonths(2)->toDateString(),
                'activo' => true,
            ],
        );

        // Pedidos de ejemplo, para que el panel y los reportes no se vean vacíos.
        if (! Pedido::where('numero_pedido', 'PED-000001')->exists()) {
            $this->crearPedidoDemo(
                $cliente,
                $direccion,
                $zonaCaba,
                'PED-000001',
                'entregado',
                [$productoModelos['Camisa de lino Amalfi'], $productoModelos['Zapato de cuero Berlín']],
            );

            $this->crearPedidoDemo(
                $cliente,
                $direccion,
                $zonaCaba,
                'PED-000002',
                'pagado',
                [$productoModelos['Cartera de mano Capri']],
            );

            $this->crearPedidoDemo(
                $cliente,
                $direccion,
                $zonaCaba,
                'PED-000003',
                'pendiente',
                [$productoModelos['Perfume floral Essenza'], $productoModelos['Anteojos de sol Positano']],
            );
        }
    }

    private function crearPedidoDemo(User $cliente, Direccion $direccion, ZonaEnvio $zona, string $numero, string $estado, array $productos): void
    {
        $items = collect($productos)->map(fn (Producto $producto) => [
            'producto' => $producto,
            'cantidad' => 1,
            'precio_unitario' => $producto->precioFinal(),
        ]);

        $subtotal = (float) $items->sum(fn ($item) => $item['cantidad'] * $item['precio_unitario']);
        $costoEnvio = (float) $zona->costo;
        $total = $subtotal + $costoEnvio;

        $pedido = Pedido::create([
            'numero_pedido' => $numero,
            'user_id' => $cliente->id,
            'direccion_id' => $direccion->id,
            'zona_envio_id' => $zona->id,
            'envio_destinatario' => $direccion->destinatario,
            'envio_calle' => $direccion->calle,
            'envio_numero' => $direccion->numero,
            'envio_piso_depto' => $direccion->piso_depto,
            'envio_ciudad' => $direccion->ciudad,
            'envio_provincia' => $direccion->provincia,
            'envio_codigo_postal' => $direccion->codigo_postal,
            'envio_telefono' => $direccion->telefono,
            'subtotal' => $subtotal,
            'descuento' => 0,
            'costo_envio' => $costoEnvio,
            'total' => $total,
            'estado' => $estado,
            'metodo_pago' => 'mercado_pago',
            'pagado_at' => in_array($estado, ['pagado', 'en_preparacion', 'enviado', 'entregado'], true) ? now() : null,
        ]);

        foreach ($items as $item) {
            $pedido->items()->create([
                'producto_id' => $item['producto']->id,
                'nombre_producto' => $item['producto']->nombre,
                'precio_unitario' => $item['precio_unitario'],
                'cantidad' => $item['cantidad'],
                'subtotal' => $item['cantidad'] * $item['precio_unitario'],
            ]);
        }
    }
}
