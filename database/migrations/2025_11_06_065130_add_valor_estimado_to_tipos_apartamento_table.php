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
        Schema::table('tipos_apartamento', function (Blueprint $table) {
            $table->decimal('valor_estimado', 18, 2)->nullable()->after('valor_m2');;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipos_apartamento', function (Blueprint $table) {
            $table->dropColumn('valor_estimado');
        });
    }
};
