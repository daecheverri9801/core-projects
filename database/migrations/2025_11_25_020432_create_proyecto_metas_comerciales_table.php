<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProyectoMetasComercialesTable extends Migration
{
    public function up()
    {
        Schema::create('proyecto_metas_comerciales', function (Blueprint $table) {
            $table->id('id_meta');
            $table->unsignedBigInteger('id_proyecto');
            $table->string('mes_anio', 7); // formato 'YYYY-MM'
            $table->integer('meta_unidades')->default(0);
            $table->decimal('meta_valor', 18, 2)->default(0);
            $table->decimal('meta_recaudos', 18, 2)->default(0)->comment('Para cuando exista módulo de recaudos');
            $table->timestamps();

            $table->foreign('id_proyecto')
                ->references('id_proyecto')
                ->on('proyectos')
                ->onDelete('cascade');

            $table->unique(['id_proyecto', 'mes_anio']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('proyecto_metas_comerciales');
    }
}
