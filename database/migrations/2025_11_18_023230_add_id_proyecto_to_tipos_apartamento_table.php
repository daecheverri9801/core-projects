<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tipos_apartamento', function (Blueprint $table) {
            // Nueva columna después del id
            $table->foreignId('id_proyecto')->nullable()
                ->after('id_tipo_apartamento')
                ->constrained('proyectos', 'id_proyecto')
                ->onDelete('cascade');
        });

        DB::table('tipos_apartamento')->update(['id_proyecto' => '1']);
        
        Schema::table('tipos_apartamento', function (Blueprint $table) {
            $table->foreignId('id_proyecto')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('tipos_apartamento', function (Blueprint $table) {
            $table->dropForeign(['id_proyecto']);
            $table->dropColumn('id_proyecto');
        });
    }
};
