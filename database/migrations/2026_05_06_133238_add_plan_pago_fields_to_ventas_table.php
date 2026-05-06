<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (!Schema::hasColumn('ventas', 'id_plan_pago_proyecto')) {
                $table->unsignedBigInteger('id_plan_pago_proyecto')
                    ->nullable()
                    ->after('id_proyecto');

                $table->foreign('id_plan_pago_proyecto')
                    ->references('id_plan_pago_proyecto')
                    ->on('planes_pago_proyecto')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('ventas', 'plan_pago_codigo')) {
                $table->string('plan_pago_codigo', 30)->nullable()->after('id_plan_pago_proyecto');
            }

            if (!Schema::hasColumn('ventas', 'plan_pago_nombre')) {
                $table->string('plan_pago_nombre', 120)->nullable()->after('plan_pago_codigo');
            }

            if (!Schema::hasColumn('ventas', 'plan_pago_tipo')) {
                $table->string('plan_pago_tipo', 40)->nullable()->after('plan_pago_nombre');
            }

            if (!Schema::hasColumn('ventas', 'plan_pago_snapshot')) {
                $table->json('plan_pago_snapshot')->nullable()->after('plan_pago_tipo');
            }

            if (!Schema::hasColumn('ventas', 'valor_total_sin_descuento')) {
                $table->decimal('valor_total_sin_descuento', 18, 2)->nullable()->after('valor_total');
            }

            if (!Schema::hasColumn('ventas', 'valor_descuento')) {
                $table->decimal('valor_descuento', 18, 2)->default(0)->after('valor_total_sin_descuento');
            }

            if (!Schema::hasColumn('ventas', 'saldo_cuota_inicial')) {
                $table->decimal('saldo_cuota_inicial', 18, 2)->nullable()->after('cuota_inicial');
            }

            if (!Schema::hasColumn('ventas', 'cuotas_manual_ci')) {
                $table->json('cuotas_manual_ci')->nullable()->after('frecuencia_cuota_inicial_meses');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            if (Schema::hasColumn('ventas', 'id_plan_pago_proyecto')) {
                $table->dropForeign(['id_plan_pago_proyecto']);
            }

            $columns = [
                'id_plan_pago_proyecto',
                'plan_pago_codigo',
                'plan_pago_nombre',
                'plan_pago_tipo',
                'plan_pago_snapshot',
                'valor_total_sin_descuento',
                'valor_descuento',
                'saldo_cuota_inicial',
                'cuotas_manual_ci',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('ventas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
