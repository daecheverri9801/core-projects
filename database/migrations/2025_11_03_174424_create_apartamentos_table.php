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
        Schema::create('apartamentos', function (Blueprint $table) {
            $table->id('id_apartamento');
            $table->string('numero', 20);
            $table->foreignId('id_tipo_apartamento')->constrained('tipos_apartamento', 'id_tipo_apartamento')->onDelete('restrict');
            $table->foreignId('id_torre')->constrained('torres', 'id_torre')->onDelete('cascade');
            $table->foreignId('id_piso_torre')->constrained('pisos_torre', 'id_piso_torre')->onDelete('cascade');
            $table->foreignId('id_estado_inmueble')->constrained('estados_inmueble', 'id_estado_inmueble')->onDelete('restrict');
            $table->decimal('valor_total', 18, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartamentos');
    }
};
