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
        Schema::table('proyectos', function (Blueprint $table) {
            $table->decimal('prima_altura_base', 15, 2)->nullable()->after('metros_construidos');
            $table->decimal('prima_altura_incremento', 15, 2)->nullable()->after('prima_altura_base');
            $table->boolean('prima_altura_activa')->default(false)->after('prima_altura_incremento');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            $table->dropColumn(['prima_altura_base', 'prima_altura_incremento', 'prima_altura_activa']);
        });
    }
};
