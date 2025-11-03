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
        Schema::create('locales', function (Blueprint $table) {
            $table->id('id_local');
            $table->string('numero', 20);
            $table->foreignId('id_estado_inmueble')->constrained('estados_inmueble', 'id_estado_inmueble')->onDelete('restrict');
            $table->decimal('area_total_local', 10, 2)->nullable();
            $table->foreignId('id_torre')->constrained('torres', 'id_torre')->onDelete('cascade');
            $table->foreignId('id_piso_torre')->constrained('pisos_torre', 'id_piso_torre')->onDelete('cascade');
            $table->decimal('valor_m2', 18, 2)->nullable();
            $table->decimal('valor_total', 18, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locales');
    }
};
