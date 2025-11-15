<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes_amortizacion_venta', function (Blueprint $table) {
            $table->id('id_plan');
            $table->unsignedBigInteger('id_venta');
            $table->string('tipo_plan', 30)->nullable();
            $table->decimal('valor_interes_anual', 18, 2)->nullable();
            $table->integer('plazo_meses')->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->string('observacion', 300)->nullable();
            $table->timestamps();

            $table->foreign('id_venta')->references('id_venta')->on('ventas')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes_amortizacion_venta');
    }
};
