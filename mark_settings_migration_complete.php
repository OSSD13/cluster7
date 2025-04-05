<?php
// Script to mark the settings migration as complete
// Run with: php mark_settings_migration_complete.php

// Bootstrap the Laravel application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Output banner
echo "=== Marking Settings Migration as Complete ===\n\n";

try {
    // Check if migrations table exists
    $migrationsTableExists = DB::getSchemaBuilder()->hasTable('migrations');
    
    if (!$migrationsTableExists) {
        echo "Creating migrations table...\n";
        DB::statement('
            CREATE TABLE migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                batch INT NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ');
        echo "Migrations table created.\n\n";
    }
    
    // Get the current max batch number
    $maxBatch = DB::table('migrations')->max('batch') ?? 0;
    $newBatch = $maxBatch + 1;
    
    // Check if the settings migration is already in the migrations table
    $migrationName = '2023_06_01_000000_create_settings_table';
    $migrationExists = DB::table('migrations')->where('migration', $migrationName)->exists();
    
    if ($migrationExists) {
        echo "The settings migration is already marked as complete.\n";
    } else {
        // Insert the migration record
        DB::table('migrations')->insert([
            'migration' => $migrationName,
            'batch' => $newBatch
        ]);
        echo "Settings migration marked as complete (batch $newBatch).\n";
    }
    
    echo "\nScript completed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}