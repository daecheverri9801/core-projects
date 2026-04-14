<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cliente_bitacoras', function (Blueprint $table) {
            $table->id('id_bitacora_cliente');

            $table->bigInteger('documento_cliente');
            $table->unsignedBigInteger('id_empleado');

            $table->date('fecha');
            $table->text('comentario');

            $table->timestamps();

            $table->foreign('documento_cliente')
                ->references('documento')
                ->on('clientes')
                ->onDelete('cascade');

            $table->foreign('id_empleado')
                ->references('id_empleado')
                ->on('empleados')
                ->onDelete('restrict');

            $table->index('documento_cliente');
            $table->index('id_empleado');
            $table->index('fecha');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cliente_bitacoras');
    }
};
