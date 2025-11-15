<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['id_estado_venta']);
            $table->dropColumn('id_estado_venta');

            $table->unsignedBigInteger('id_estado_inmueble')->after('id_forma_pago');
            $table->foreign('id_estado_inmueble')->references('id_estado_inmueble')->on('estados_inmueble');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropForeign(['id_estado_inmueble']);
            $table->dropColumn('id_estado_inmueble');

            $table->unsignedBigInteger('id_estado_venta')->after('id_forma_pago');
            $table->foreign('id_estado_venta')->references('id_estado_venta')->on('estados_venta');
        });
    }
};
