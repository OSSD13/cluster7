<?php
// Fix Trello settings script
// Run with: php fix_trello_settings.php

// Bootstrap the application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Output banner
echo "=== Fixing Trello API Settings ===\n\n";

// Check if we have a trello_api_secret setting
$apiSecret = DB::table('settings')->where('key', 'trello_api_secret')->first();

if ($apiSecret) {
    echo "Found existing trello_api_secret entry.\n";
    echo "Value length: " . strlen($apiSecret->value) . "\n";
    
    // Copy the value to trello_api_token
    $result = DB::table('settings')->updateOrInsert(
        ['key' => 'trello_api_token'],
        [
            'value' => $apiSecret->value,
            'description' => 'Trello API Token',
            'created_at' => now(),
            'updated_at' => now(),
        ]
    );
    
    echo "Copied value to trello_api_token: " . ($result ? "Success" : "Failed") . "\n";
    
    // List all trello settings
    echo "\nCurrent Trello API settings:\n";
    $trelloSettings = DB::table('settings')->where('key', 'like', 'trello%')->get();
    foreach ($trelloSettings as $setting) {
        echo "- {$setting->key}: " . substr($setting->value, 0, 4) . "..." . substr($setting->value, -4) . " (length: " . strlen($setting->value) . ")\n";
    }
    
    echo "\nYou should now be able to connect to Trello API. If you still have issues, try clearing the database entry and adding it again through the UI.\n";
} else {
    echo "No trello_api_secret entry found.\n";
    
    // Check for trello_api_token
    $apiToken = DB::table('settings')->where('key', 'trello_api_token')->first();
    if ($apiToken) {
        echo "Found trello_api_token with value length: " . strlen($apiToken->value) . "\n";
    } else {
        echo "No trello_api_token found either. You need to set up your Trello API credentials in the settings page.\n";
    }
}

echo "\n=== Done ===\n"; 