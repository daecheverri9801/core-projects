<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bloqueos_inmuebles', function (Blueprint $table) {
            $table->id('id_bloqueo');

            $table->unsignedBigInteger('id_inmueble');
            $table->enum('inmueble_tipo', ['apartamento', 'local']);

            $table->unsignedBigInteger('id_empleado')
                ->nullable()
                ->comment('Empleado que abrió la operación');

            $table->timestamp('bloqueado_en')->default(now());
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('released_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bloqueos_inmuebles');
    }
};
