<?php
// register_migrations.php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Registrando migraciones existentes...\n";

// Obtener archivos de migración
$migrationFiles = scandir(__DIR__ . '/database/migrations');
$migrations = [];
$batch = 1;

foreach ($migrationFiles as $file) {
    if ($file !== '.' && $file !== '..' && pathinfo($file, PATHINFO_EXTENSION) === 'php') {
        $migration = str_replace('.php', '', $file);
        $migrations[$migration] = ['migration' => $migration, 'batch' => $batch];
    }
}

// Ordenar por fecha (viene en el nombre del archivo)
ksort($migrations);

// Insertar en la tabla migrations
DB::table('migrations')->truncate(); // Limpiar por si acaso

foreach ($migrations as $migration) {
    DB::table('migrations')->insert($migration);
    echo "✓ Registrada: {$migration['migration']}\n";
}

$total = count($migrations);
echo "\n✅ Se registraron $total migraciones correctamente.\n";