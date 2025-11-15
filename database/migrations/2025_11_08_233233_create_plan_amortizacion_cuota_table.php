<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes_amortizacion_cuota', function (Blueprint $table) {
            $table->id('id_cuota');
            $table->unsignedBigInteger('id_plan');
            $table->integer('numero_cuota')->nullable();
            $table->date('fecha_vencimiento')->nullable();
            $table->decimal('valor_cuota', 18, 2)->nullable();
            $table->decimal('valor_interes', 18, 2)->nullable();
            $table->decimal('valor_capital', 18, 2)->nullable();
            $table->decimal('saldo', 18, 2)->nullable();
            $table->string('estado', 20)->nullable();
            $table->timestamps();

            $table->foreign('id_plan')->references('id_plan')->on('planes_amortizacion_venta')->onDelete('cascade');
        });

        // Agregar FK de id_cuota en pagos después de crear planes_amortizacion_cuota
        Schema::table('pagos', function (Blueprint $table) {
            $table->foreign('id_cuota')->references('id_cuota')->on('planes_amortizacion_cuota')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropForeign(['id_cuota']);
        });
        Schema::dropIfExists('planes_amortizacion_cuota');
    }
};
