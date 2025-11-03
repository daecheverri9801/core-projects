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
        Schema::create('pisos_torre', function (Blueprint $table) {
            $table->id('id_piso_torre');
            $table->integer('nivel');
            $table->foreignId('id_torre')->constrained('torres', 'id_torre')->onDelete('cascade');
            $table->string('uso', 40)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pisos_torre');
    }
};
