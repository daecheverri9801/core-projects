<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planes_pago_proyecto', function (Blueprint $table) {
            $table->id('id_plan_pago_proyecto');

            $table->foreignId('id_proyecto')
                ->constrained('proyectos', 'id_proyecto')
                ->cascadeOnDelete();

            $table->string('codigo', 30);
            $table->string('nombre', 120);
            $table->text('descripcion')->nullable();

            $table->unsignedSmallInteger('orden')->default(1);

            /*
             * Tipos soportados:
             * - cuota_inicial_mensual: Plan 01 / Plan 02
             * - cuota_inicial_contado: Plan 03
             * - pago_total_diferido: Plan 04
             * - especial_manual: Plan 05
             */
            $table->string('tipo_plan', 40)->default('cuota_inicial_mensual');

            /*
             * La separación siempre hace parte de la cuota inicial,
             * saldo contado o precio con descuento según el plan.
             */
            $table->decimal('valor_separacion', 18, 2)->default(0);

            /*
             * Porcentaje de cuota inicial.
             * Para Plan 03 aplica aunque se pague de contado.
             */
            $table->decimal('porcentaje_cuota_inicial', 7, 4)->nullable();

            /*
             * Para planes con cuotas mensuales normales.
             * En plan especial puede quedar null porque se define en la venta.
             */
            $table->unsignedSmallInteger('plazo_cuota_inicial_meses')->nullable();
            $table->unsignedSmallInteger('frecuencia_cuota_inicial_meses')->default(1);

            /*
             * Para planes tipo pago total diferido.
             * Ejemplo: pagar saldo en 60 días.
             */
            $table->unsignedSmallInteger('plazo_pago_total_dias')->nullable();

            /*
             * Porcentaje que queda para escritura.
             * Plan 04 sería 0.
             */
            $table->decimal('porcentaje_escritura', 7, 4)->default(0);

            /*
             * Descuentos:
             * tipo_descuento: ninguno | valor_fijo | porcentaje
             * base_descuento: ninguna | precio_total | cuota_inicial
             */
            $table->string('tipo_descuento', 30)->default('ninguno');
            $table->decimal('valor_descuento', 18, 4)->nullable();
            $table->string('base_descuento', 30)->default('ninguna');

            /*
             * Beneficios o compromisos comerciales.
             * Ejemplo: Smart TV 65".
             */
            $table->text('beneficio_comercial')->nullable();

            /*
             * Para Plan 05.
             */
            $table->boolean('permite_plazo_manual')->default(false);
            $table->boolean('permite_cuotas_manuales')->default(false);

            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->unique(['id_proyecto', 'codigo']);
            $table->index(['id_proyecto', 'activo']);
            $table->index(['id_proyecto', 'tipo_plan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planes_pago_proyecto');
    }
};
