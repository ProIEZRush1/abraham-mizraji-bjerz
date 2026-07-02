<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('numero_pedido')->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('direccion_id')->nullable()->constrained('direcciones')->nullOnDelete();
            $table->foreignId('zona_envio_id')->nullable()->constrained('zonas_envio')->nullOnDelete();
            $table->foreignId('cupon_id')->nullable()->constrained('cupones')->nullOnDelete();
            $table->string('cupon_codigo')->nullable();

            // Copia de la dirección de envío al momento de la compra (no depende de ediciones futuras).
            $table->string('envio_destinatario')->nullable();
            $table->string('envio_calle')->nullable();
            $table->string('envio_numero')->nullable();
            $table->string('envio_piso_depto')->nullable();
            $table->string('envio_ciudad')->nullable();
            $table->string('envio_provincia')->nullable();
            $table->string('envio_codigo_postal')->nullable();
            $table->string('envio_telefono')->nullable();

            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('descuento', 10, 2)->default(0);
            $table->decimal('costo_envio', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);

            $table->string('estado')->default('pendiente');
            $table->string('metodo_pago')->default('mercado_pago');
            $table->string('mp_preference_id')->nullable();
            $table->string('mp_payment_id')->nullable();
            $table->timestamp('pagado_at')->nullable();
            $table->text('notas')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
