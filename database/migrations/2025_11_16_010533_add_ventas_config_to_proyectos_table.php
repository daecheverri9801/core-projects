<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            // Mínimo % cuota inicial
            if (!Schema::hasColumn('proyectos', 'porcentaje_cuota_inicial_min')) {
                $table->decimal('porcentaje_cuota_inicial_min', 5, 2)->default(0);
            }

            // Plazo máximo de pago de cuota inicial (meses)
            if (!Schema::hasColumn('proyectos', 'plazo_cuota_inicial_meses')) {
                $table->unsignedInteger('plazo_cuota_inicial_meses')->default(0);
            }

            // Valor mínimo de separación
            if (!Schema::hasColumn('proyectos', 'valor_min_separacion')) {
                $table->decimal('valor_min_separacion', 18, 2)->default(0);
            }

            // Plazo máximo de separación (días)
            if (!Schema::hasColumn('proyectos', 'plazo_max_separacion_dias')) {
                $table->unsignedInteger('plazo_max_separacion_dias')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropColumn([
                'porcentaje_cuota_inicial_min',
                'plazo_cuota_inicial_meses',
                'valor_min_separacion',
                'plazo_max_separacion_dias',
            ]);
        });
    }
};
