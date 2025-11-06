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
        Schema::table('apartamentos', function (Blueprint $table) {
            $table->decimal('prima_altura', 15, 2)->nullable()->after('valor_total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apartamentos', function (Blueprint $table) {
            $table->dropColumn('prima_altura');
        });
    }
};
