<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('direcciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('etiqueta')->default('Casa');
            $table->string('destinatario');
            $table->string('calle');
            $table->string('numero');
            $table->string('piso_depto')->nullable();
            $table->string('ciudad');
            $table->string('provincia');
            $table->string('codigo_postal');
            $table->string('telefono')->nullable();
            $table->boolean('predeterminada')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('direcciones');
    }
};
