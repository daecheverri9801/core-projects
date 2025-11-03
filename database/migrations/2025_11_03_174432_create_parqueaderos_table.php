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
        Schema::create('parqueaderos', function (Blueprint $table) {
            $table->id('id_parqueadero');
            $table->string('numero', 20);
            $table->string('tipo', 20);
            $table->foreignId('id_apartamento')->nullable()->constrained('apartamentos', 'id_apartamento')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parqueaderos');
    }
};
