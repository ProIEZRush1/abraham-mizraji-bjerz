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
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            // Usuario que realizó la acción (snapshot del nombre por si se elimina).
            $table->foreignId('user_id')->nullable()->index();
            $table->string('user_name')->nullable();
            // Acción registrada: created | updated | deleted.
            $table->string('event')->index();
            // Modelo afectado.
            $table->string('auditable_type')->index();
            $table->unsignedBigInteger('auditable_id')->nullable()->index();
            // Cambios registrados (antes / después).
            $table->json('changes')->nullable();
            // Contexto de la petición.
            $table->string('ip_address', 45)->nullable();
            $table->string('url')->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
