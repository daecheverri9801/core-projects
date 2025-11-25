<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMetasTable extends Migration
{
    public function up()
    {
        Schema::create('metas', function (Blueprint $table) {
            $table->id('id_meta');

            $table->unsignedBigInteger('id_proyecto')->nullable();
            $table->unsignedBigInteger('id_empleado')->nullable(); // meta por asesor (opcional)

            $table->string('tipo'); // ventas, recaudos, unidades
            $table->integer('mes'); // 1–12
            $table->integer('ano'); // año meta

            $table->decimal('meta_valor', 18, 2)->default(0); // $ meta
            $table->integer('meta_unidades')->default(0);     // unidades meta

            $table->timestamps();

            $table->foreign('id_proyecto')->references('id_proyecto')->on('proyectos')->onDelete('set null');
            $table->foreign('id_empleado')->references('id_empleado')->on('empleados')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('metas');
    }
}
