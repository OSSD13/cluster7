<?php
// Script to create the settings table directly
// Run with: php create_settings_table.php

// Bootstrap the Laravel application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

// Output banner
echo "=== Creating Settings Table ===\n\n";

try {
    // Check if the table already exists
    $tableExists = Schema::hasTable('settings');
    
    if ($tableExists) {
        echo "Settings table already exists.\n";
    } else {
        // Create the settings table
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });
        
        echo "Settings table created successfully!\n";
    }
    
    // Insert default settings if needed
    $settingsCount = DB::table('settings')->count();
    echo "Current settings count: $settingsCount\n";
    
    echo "\nScript completed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}