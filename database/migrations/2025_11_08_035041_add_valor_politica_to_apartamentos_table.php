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
            $table->decimal('valor_politica', 15, 2)->nullable()->after('valor_total');
            $table->decimal('valor_final', 15, 2)->nullable()->after('valor_politica');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('apartamentos', function (Blueprint $table) {
            $table->dropColumn('valor_politica');
            $table->dropColumn('valor_final');
        });
    }
};
