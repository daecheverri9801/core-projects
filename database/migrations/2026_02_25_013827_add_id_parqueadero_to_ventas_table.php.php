<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->foreignId('id_parqueadero')
                ->nullable()
                ->after('id_apartamento') 
                ->constrained('parqueaderos', 'id_parqueadero')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropUnique('ventas_id_parqueadero_unique');
            $table->dropConstrainedForeignId('id_parqueadero');
        });
    }
};