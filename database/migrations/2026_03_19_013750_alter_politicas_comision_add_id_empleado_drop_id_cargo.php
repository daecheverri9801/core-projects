<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('politicas_comision', function (Blueprint $table) {
            $table->foreignId('id_empleado')
                ->nullable()
                ->after('id_proyecto')
                ->constrained('empleados', 'id_empleado')
                ->cascadeOnDelete();
        });

        Schema::table('politicas_comision', function (Blueprint $table) {
            if (Schema::hasColumn('politicas_comision', 'id_cargo')) {
                $table->dropConstrainedForeignId('id_cargo');
            }
        });
    }

    public function down(): void
    {
        Schema::table('politicas_comision', function (Blueprint $table) {
            $table->foreignId('id_cargo')
                ->nullable()
                ->after('id_proyecto')
                ->constrained('cargos', 'id_cargo')
                ->cascadeOnDelete();

            if (Schema::hasColumn('politicas_comision', 'id_empleado')) {
                $table->dropConstrainedForeignId('id_empleado');
            }
        });
    }
};
