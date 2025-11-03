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
        Schema::create('tipos_apartamento', function (Blueprint $table) {
            $table->id('id_tipo_apartamento');
            $table->string('nombre', 100);
            $table->decimal('area_construida', 10, 2)->nullable();
            $table->decimal('area_privada', 10, 2)->nullable();
            $table->smallInteger('cantidad_habitaciones')->nullable();
            $table->smallInteger('cantidad_banos')->nullable();
            $table->decimal('valor_m2', 18, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipos_apartamento');
    }
};
