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
        Schema::create('empleados', function (Blueprint $table) {
            $table->id('id_empleado');
            $table->string('nombre', 120);
            $table->string('apellido', 120);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->string('telefono', 30)->nullable();
            $table->foreignId('id_cargo')->constrained('cargos', 'id_cargo')->onDelete('restrict');
            $table->foreignId('id_dependencia')->constrained('dependencias', 'id_dependencia')->onDelete('restrict');
            $table->boolean('estado')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empleados');
    }
};
