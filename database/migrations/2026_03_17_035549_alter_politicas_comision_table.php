<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('politicas_comision', function (Blueprint $table) {
            if (Schema::hasColumn('politicas_comision', 'aplica_a')) {
                $table->dropColumn([
                    'aplica_a',
                    'base_calculo',
                    'valor_fijo',
                    'minimo_venta_estado',
                    'descripcion',
                ]);
            }
        });

        Schema::table('politicas_comision', function (Blueprint $table) {
            $table->foreignId('id_cargo')
                ->nullable()
                ->after('id_proyecto')
                ->constrained('cargos', 'id_cargo')
                ->cascadeOnDelete();

            $table->string('tipo_comision', 30)
                ->nullable()
                ->after('id_cargo');
        });
    }

    public function down(): void
    {
        Schema::table('politicas_comision', function (Blueprint $table) {
            $table->string('aplica_a', 50)->nullable();
            $table->string('base_calculo', 50)->nullable();
            $table->decimal('valor_fijo', 18, 2)->nullable();
            $table->string('minimo_venta_estado', 30)->nullable();
            $table->string('descripcion', 300)->nullable();

            $table->dropConstrainedForeignId('id_cargo');
            $table->dropColumn('tipo_comision');
        });
    }
};
