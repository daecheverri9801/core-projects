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
            if (!Schema::hasColumn('proyectos', 'activo')) {
                $table->boolean('activo')->default(true)->after('id_ubicacion');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proyectos', function (Blueprint $table) {
            if (Schema::hasColumn('proyectos', 'activo')) {
                $table->dropColumn('activo');
            }
        });
    }
};
