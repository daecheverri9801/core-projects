<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            // Tipo de operación: venta o separacion
            $table->string('tipo_operacion', 20)
                ->default('venta')
                ->after('id_venta');

            // Solo para separacion
            $table->decimal('valor_separacion', 18, 2)
                ->nullable()
                ->after('valor_total');

            $table->date('fecha_limite_separacion')
                ->nullable()
                ->after('valor_separacion');
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn(['tipo_operacion', 'valor_separacion', 'fecha_limite_separacion']);
        });
    }
};
