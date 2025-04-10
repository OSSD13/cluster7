<?php
// Script to test fetching Trello boards
// Run with: php test_trello_boards.php

// Bootstrap the Laravel application
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;
use App\Models\Setting;

// Output banner
echo "=== Testing Trello Boards Access ===\n\n";

try {
    // Get credentials from settings
    $apiKey = Setting::get('trello_api_key');
    $apiToken = Setting::get('trello_api_token');

    if (empty($apiKey) || empty($apiToken)) {
        throw new Exception('Trello credentials not found in settings');
    }

    echo "Using API Key: " . substr($apiKey, 0, 4) . "..." . substr($apiKey, -4) . "\n";
    echo "Using API Token: " . substr($apiToken, 0, 4) . "..." . substr($apiToken, -4) . "\n\n";

    // Fetch boards from Trello API
    $response = Http::withOptions(['verify' => false])->get('https://api.trello.com/1/members/me/boards', [
        'key' => $apiKey,
        'token' => $apiToken,
        'fields' => 'name,url,idOrganization,closed',
        'filter' => 'open'
    ]);

    if ($response->successful()) {
        $boards = $response->json();
        echo "Successfully fetched " . count($boards) . " boards:\n\n";
        
        foreach ($boards as $board) {
            echo "- " . $board['name'] . "\n";
            echo "  URL: " . $board['url'] . "\n";
            echo "  Organization ID: " . $board['idOrganization'] . "\n";
            echo "  Closed: " . ($board['closed'] ? 'Yes' : 'No') . "\n\n";
        }
    } else {
        echo "Failed to fetch boards!\n";
        echo "Status: " . $response->status() . "\n";
        echo "Error: " . $response->body() . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\nScript completed.\n"; 