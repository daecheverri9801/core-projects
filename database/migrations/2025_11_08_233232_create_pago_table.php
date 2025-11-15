<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id('id_pago');
            $table->timestamp('fecha')->useCurrent();
            $table->unsignedBigInteger('id_venta');
            $table->string('referencia_pago', 60)->nullable();
            $table->unsignedBigInteger('id_concepto_pago')->nullable();
            $table->unsignedBigInteger('id_medio_pago')->nullable();
            $table->string('descripcion')->nullable();
            $table->decimal('valor', 18, 2)->nullable();
            $table->unsignedBigInteger('id_cuota')->nullable();
            $table->timestamps();

            $table->foreign('id_venta')->references('id_venta')->on('ventas')->onDelete('cascade');
            $table->foreign('id_concepto_pago')->references('id_concepto_pago')->on('conceptos_pago')->onDelete('restrict');
            $table->foreign('id_medio_pago')->references('id_medio_pago')->on('medios_pago')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};