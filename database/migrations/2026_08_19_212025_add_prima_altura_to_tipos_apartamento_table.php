<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipos_apartamento', function (Blueprint $table) {
            /*
             * NULL significa que el tipo todavía no ha sido migrado
             * y debe utilizar temporalmente la configuración antigua
             * Proyecto + Torre.
             *
             * true / false significa que ya utiliza la nueva configuración.
             */
            $table->boolean('prima_altura_activa')->nullable();

            $table->unsignedSmallInteger('nivel_inicio_prima')->nullable();

            $table->decimal('prima_altura_base', 15, 2)->nullable();

            $table->decimal('prima_altura_incremento', 15, 2)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('tipos_apartamento', function (Blueprint $table) {
            $table->dropColumn([
                'prima_altura_activa',
                'nivel_inicio_prima',
                'prima_altura_base',
                'prima_altura_incremento',
            ]);
        });
    }
};
