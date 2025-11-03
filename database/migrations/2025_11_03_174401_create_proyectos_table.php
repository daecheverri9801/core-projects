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
        Schema::create('proyectos', function (Blueprint $table) {
            $table->id('id_proyecto');
            $table->string('nombre', 150);
            $table->text('descripcion', 300)->nullable();
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_finalizacion')->nullable();
            $table->decimal('presupuesto_inicial', 18, 2)->nullable();
            $table->decimal('presupuesto_final', 18, 2)->nullable();
            $table->decimal('metros_construidos', 18, 2)->nullable();
            $table->integer('cantidad_locales')->nullable();
            $table->integer('cantidad_apartamentos')->nullable();
            $table->integer('cantidad_parqueaderos_vehiculo')->nullable();
            $table->integer('cantidad_parqueaderos_moto')->nullable();
            $table->smallInteger('estrato')->nullable();
            $table->smallInteger('numero_pisos')->nullable();
            $table->smallInteger('numero_torres')->nullable();
            $table->decimal('porcentaje_cuota_inicial_min', 5, 2)->nullable();
            $table->decimal('valor_min_separacion', 18, 2)->nullable();
            $table->smallInteger('plazo_cuota_inicial_meses')->nullable();
            $table->foreignId('id_estado')->constrained('estados', 'id_estado')->onDelete('restrict');
            $table->foreignId('id_ubicacion')->constrained('ubicaciones', 'id_ubicacion')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proyectos');
    }
};
