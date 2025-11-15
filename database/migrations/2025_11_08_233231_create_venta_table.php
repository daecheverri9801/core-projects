<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id('id_venta');
            $table->unsignedBigInteger('id_empleado');
            $table->unsignedBigInteger('documento_cliente');
            $table->date('fecha_venta');
            $table->date('fecha_vencimiento')->nullable();
            $table->unsignedBigInteger('id_apartamento')->nullable();
            $table->unsignedBigInteger('id_local')->nullable();
            $table->unsignedBigInteger('id_proyecto')->nullable();
            $table->unsignedBigInteger('id_forma_pago');
            $table->unsignedBigInteger('id_estado_venta');
            $table->decimal('cuota_inicial', 18, 2)->nullable();
            $table->decimal('valor_restante', 18, 2)->nullable();
            $table->string('descripcion', 300)->nullable();
            $table->decimal('valor_base', 18, 2)->nullable();
            $table->decimal('iva', 18, 2)->nullable();
            $table->decimal('valor_total', 18, 2)->nullable();
            $table->timestamps();

            $table->foreign('id_empleado')->references('id_empleado')->on('empleados')->onDelete('restrict');
            $table->foreign('documento_cliente')->references('documento')->on('clientes')->onDelete('restrict');
            $table->foreign('id_apartamento')->references('id_apartamento')->on('apartamentos')->onDelete('restrict');
            $table->foreign('id_local')->references('id_local')->on('locales')->onDelete('restrict');
            $table->foreign('id_proyecto')->references('id_proyecto')->on('proyectos')->onDelete('restrict');
            $table->foreign('id_forma_pago')->references('id_forma_pago')->on('formas_pago')->onDelete('restrict');
            $table->foreign('id_estado_venta')->references('id_estado_venta')->on('estados_venta')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventas');
    }
};
