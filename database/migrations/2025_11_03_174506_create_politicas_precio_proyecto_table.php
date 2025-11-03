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
        Schema::create('politicas_precio_proyecto', function (Blueprint $table) {
            $table->id('id_politica_precio');
            $table->foreignId('id_proyecto')->constrained('proyectos', 'id_proyecto')->onDelete('cascade');
            $table->integer('ventas_por_escalon')->nullable();
            $table->decimal('porcentaje_aumento', 6, 3)->nullable();
            $table->date('aplica_desde')->nullable();
            $table->boolean('estado')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('politicas_precio_proyecto');
    }
};
