<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pagos_payments', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->string('customer_name')->nullable();
            $table->string('customer_email')->nullable();
            $table->unsignedBigInteger('amount'); // monto en la unidad mínima (centavos)
            $table->string('currency', 3)->default('mxn');
            $table->string('status')->default('pendiente'); // pendiente | pagado | cancelado
            $table->string('stripe_session_id')->nullable()->index();
            $table->string('stripe_payment_intent')->nullable();
            $table->text('checkout_url')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos_payments');
    }
};
