<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->unsignedBigInteger('id_empleado_asesor')
                ->nullable()
                ->after('correo');

            $table->foreign('id_empleado_asesor')
                ->references('id_empleado')
                ->on('empleados')
                ->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::table('clientes', function (Blueprint $table) {
            $table->dropForeign(['id_empleado_asesor']);
            $table->dropColumn('id_empleado_asesor');
        });
    }
};