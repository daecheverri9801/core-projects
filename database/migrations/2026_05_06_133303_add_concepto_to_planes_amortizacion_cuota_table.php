<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('planes_amortizacion_cuota', function (Blueprint $table) {
            if (!Schema::hasColumn('planes_amortizacion_cuota', 'concepto')) {
                $table->string('concepto', 80)
                    ->nullable()
                    ->after('fecha_vencimiento');
            }
        });
    }

    public function down(): void
    {
        Schema::table('planes_amortizacion_cuota', function (Blueprint $table) {
            if (Schema::hasColumn('planes_amortizacion_cuota', 'concepto')) {
                $table->dropColumn('concepto');
            }
        });
    }
};
