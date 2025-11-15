<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id('id_cliente');
            $table->string('nombre', 150);
            $table->unsignedBigInteger('id_tipo_cliente');
            $table->unsignedBigInteger('id_tipo_documento');
            $table->bigInteger('documento', 40)->primary();
            $table->string('direccion', 200)->nullable();
            $table->string('telefono', 30)->nullable();
            $table->string('correo', 150)->nullable();
            $table->timestamps();

            $table->foreign('id_tipo_cliente')->references('id_tipo_cliente')->on('tipos_cliente')->onDelete('restrict');
            $table->foreign('id_tipo_documento')->references('id_tipo_documento')->on('tipos_documento')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
