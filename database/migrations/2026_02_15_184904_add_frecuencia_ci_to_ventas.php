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
        Schema::table('ventas', function (Blueprint $table) {
            // Cada cuántos meses paga la cuota inicial (1 = mensual)
            $table->unsignedSmallInteger('frecuencia_cuota_inicial_meses')
                ->nullable()
                ->default(1)
                ->after('plazo_cuota_inicial_meses');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn('frecuencia_cuota_inicial_meses');
        });
    }
};
