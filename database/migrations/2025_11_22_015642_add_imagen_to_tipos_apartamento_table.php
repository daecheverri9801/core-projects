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
            $table->string('imagen')->nullable()->after('valor_estimado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tipos_apartamento', function (Blueprint $table) {
            $table->dropColumn('imagen');
        });
    }
};
