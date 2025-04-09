<?php
// Script to fix Trello credentials in settings table
// Run with: php fix_trello_credentials.php

// Bootstrap the Laravel application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Output banner
echo "=== Fixing Trello Credentials ===\n\n";

try {
    // Get credentials from .env
    $apiKey = env('TRELLO_API_KEY');
    $apiToken = env('TRELLO_API_TOKEN');

    if (empty($apiKey) || empty($apiToken)) {
        throw new Exception('Trello credentials not found in .env file');
    }

    // Update or insert API key
    DB::table('settings')->updateOrInsert(
        ['key' => 'trello_api_key'],
        [
            'value' => $apiKey,
            'description' => 'Trello API Key',
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );

    // Update or insert API token
    DB::table('settings')->updateOrInsert(
        ['key' => 'trello_api_token'],
        [
            'value' => $apiToken,
            'description' => 'Trello API Token',
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );

    echo "Trello credentials updated successfully!\n";
    echo "API Key: " . substr($apiKey, 0, 4) . "..." . substr($apiKey, -4) . "\n";
    echo "API Token: " . substr($apiToken, 0, 4) . "..." . substr($apiToken, -4) . "\n";

    // Verify the settings
    $settings = DB::table('settings')
        ->whereIn('key', ['trello_api_key', 'trello_api_token'])
        ->get();

    echo "\nCurrent Trello settings in database:\n";
    foreach ($settings as $setting) {
        echo "- {$setting->key}: " . substr($setting->value, 0, 4) . "..." . substr($setting->value, -4) . "\n";
    }

    echo "\nScript completed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
} 