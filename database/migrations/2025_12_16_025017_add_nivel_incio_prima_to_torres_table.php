<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('torres', function (Blueprint $table) {
            $table->unsignedSmallInteger('nivel_inicio_prima')
                ->default(2)
                ->after('numero_pisos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('torres', function (Blueprint $table) {
            $table->dropColumn('nivel_inicio_prima');
        });
    }
};
