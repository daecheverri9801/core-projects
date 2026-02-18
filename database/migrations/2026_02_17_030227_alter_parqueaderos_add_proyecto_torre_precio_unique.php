<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parqueaderos', function (Blueprint $table) {

            // columnas nuevas
            $table->foreignId('id_proyecto')
                ->nullable()
                ->after('tipo')
                ->constrained('proyectos', 'id_proyecto')
                ->nullOnDelete();

            $table->foreignId('id_torre')
                ->nullable()
                ->after('id_proyecto')
                ->constrained('torres', 'id_torre')
                ->nullOnDelete();

            $table->decimal('precio', 14, 2)
                ->nullable()
                ->after('id_torre');

            // unique por torre (numero puede repetirse en otra torre)
            $table->unique(['id_torre', 'numero'], 'parqueaderos_torre_numero_unique');
        });
    }

    public function down(): void
    {
        Schema::table('parqueaderos', function (Blueprint $table) {

            // borrar unique creada por nombre
            $table->dropUnique('parqueaderos_torre_numero_unique');

            // dropear FKs y columnas (Blueprint se encarga del drop FK al drop column en la mayoría,
            // pero en PG es mejor ser explícito si falla)
            $table->dropConstrainedForeignId('id_torre');
            $table->dropConstrainedForeignId('id_proyecto');

            $table->dropColumn('precio');
        });
    }
};
