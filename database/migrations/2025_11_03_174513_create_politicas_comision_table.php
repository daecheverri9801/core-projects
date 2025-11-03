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
        Schema::create('politicas_comision', function (Blueprint $table) {
            $table->id('id_politica_comision');
            $table->foreignId('id_proyecto')->constrained('proyectos', 'id_proyecto')->onDelete('cascade');
            $table->string('aplica_a', 50)->nullable();
            $table->string('base_calculo', 50)->nullable();
            $table->decimal('porcentaje', 6, 3)->nullable();
            $table->decimal('valor_fijo', 18, 2)->nullable();
            $table->string('minimo_venta_estado', 30)->nullable();
            $table->string('descripcion', 300)->nullable();
            $table->date('vigente_desde')->nullable();
            $table->date('vigente_hasta')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('politicas_comision');
    }
};
