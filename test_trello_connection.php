<?php
// Script to test Trello API connection
// Run with: php test_trello_connection.php

// Bootstrap the Laravel application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;
use App\Models\Setting;

// Output banner
echo "=== Testing Trello API Connection ===\n\n";

try {
    // Get credentials from settings
    $apiKey = Setting::get('trello_api_key');
    $apiToken = Setting::get('trello_api_token');

    if (empty($apiKey) || empty($apiToken)) {
        throw new Exception('Trello credentials not found in settings');
    }

    echo "Using API Key: " . substr($apiKey, 0, 4) . "..." . substr($apiKey, -4) . "\n";
    echo "Using API Token: " . substr($apiToken, 0, 4) . "..." . substr($apiToken, -4) . "\n\n";

    // Test connection to Trello API
    $response = Http::withOptions(['verify' => false])->get('https://api.trello.com/1/members/me', [
        'key' => $apiKey,
        'token' => $apiToken
    ]);

    if ($response->successful()) {
        $data = $response->json();
        echo "Connection successful!\n";
        echo "Trello User: " . $data['fullName'] . " (" . $data['username'] . ")\n";
        echo "Email: " . $data['email'] . "\n";
    } else {
        echo "Connection failed!\n";
        echo "Status: " . $response->status() . "\n";
        echo "Error: " . $response->body() . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nScript completed.\n"; 