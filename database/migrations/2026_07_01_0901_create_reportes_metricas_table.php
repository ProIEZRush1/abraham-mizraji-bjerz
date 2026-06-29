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
        Schema::create('reportes_metricas', function (Blueprint $table) {
            $table->id();
            $table->string('categoria')->index();
            $table->string('etiqueta')->nullable();
            $table->decimal('valor', 15, 2)->default(0);
            $table->date('ocurrido_el')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reportes_metricas');
    }
};
