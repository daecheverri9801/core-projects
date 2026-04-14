<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            if (!Schema::hasColumn('pagos', 'comprobante_path')) {
                $table->string('comprobante_path')->nullable()->after('id_cuota');
            }

            if (!Schema::hasColumn('pagos', 'comprobante_nombre_original')) {
                $table->string('comprobante_nombre_original', 255)->nullable()->after('comprobante_path');
            }

            if (!Schema::hasColumn('pagos', 'comprobante_mime')) {
                $table->string('comprobante_mime', 100)->nullable()->after('comprobante_nombre_original');
            }

            if (!Schema::hasColumn('pagos', 'comprobante_size')) {
                $table->unsignedBigInteger('comprobante_size')->nullable()->after('comprobante_mime');
            }
        });

        Schema::table('pagos', function (Blueprint $table) {
            $foreignKeys = collect(DB::select("
                SELECT conname
                FROM pg_constraint
                WHERE conrelid = 'pagos'::regclass
                AND contype = 'f'
            "))->pluck('conname')->values();

            if (!$foreignKeys->contains(fn($name) => str_contains($name, 'pagos_id_cuota_foreign'))) {
                $table->foreign('id_cuota')
                    ->references('id_cuota')
                    ->on('planes_amortizacion_cuota')
                    ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            try {
                $table->dropForeign(['id_cuota']);
            } catch (\Throwable $e) {
            }

            $columns = [
                'comprobante_path',
                'comprobante_nombre_original',
                'comprobante_mime',
                'comprobante_size',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('pagos', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
