<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\TrelloService;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class TestTrelloConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trello:test-connection {--key=} {--token=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test the Trello API connection to help diagnose issues';

    /**
     * Execute the console command.
     */
    public function handle(TrelloService $trelloService)
    {
        $this->info('Testing Trello API connection...');
        
        // Get credentials from arguments or from database
        $key = $this->option('key');
        $token = $this->option('token');
        
        if (!$key) {
            $key = Setting::where('key', 'trello_api_key')->first()?->value;
            $this->info('Using API key from database: ' . ($key ? substr($key, 0, 4) . '...' : 'Not found'));
        }
        
        if (!$token) {
            $token = Setting::where('key', 'trello_api_token')->first()?->value;
            $this->info('Using API token from database: ' . ($token ? substr($token, 0, 4) . '...' : 'Not found'));
        }
        
        if (!$key || !$token) {
            $this->error('Missing API credentials. Please provide both key and token.');
            return 1;
        }
        
        // Test basic validation
        $this->info('Checking credential format:');
        $this->info('- API key length: ' . strlen($key));
        $this->info('- API token length: ' . strlen($token));
        
        // Test API connection
        $this->info('Testing API connection to https://api.trello.com/1/members/me');
        
        try {
            $response = Http::withOptions([
                'verify' => false,
                'timeout' => 15,
            ])->get('https://api.trello.com/1/members/me', [
                'key' => $key,
                'token' => $token
            ]);
            
            if ($response->successful()) {
                $data = $response->json();
                $this->info('✅ Connection successful!');
                $this->info('Connected as: ' . ($data['fullName'] ?? 'Unknown') . ' (' . ($data['username'] ?? 'Unknown') . ')');
                
                // Try to get boards to verify further
                $this->info('Testing boards access...');
                $boardsResponse = Http::withOptions([
                    'verify' => false,
                    'timeout' => 15,
                ])->get('https://api.trello.com/1/members/me/boards', [
                    'key' => $key,
                    'token' => $token,
                    'filter' => 'open',
                    'fields' => 'name'
                ]);
                
                if ($boardsResponse->successful()) {
                    $boards = $boardsResponse->json();
                    $this->info('✅ Successfully retrieved ' . count($boards) . ' boards');
                    
                    if (count($boards) > 0) {
                        $this->info('First 3 boards:');
                        foreach (array_slice($boards, 0, 3) as $board) {
                            $this->info('- ' . $board['name']);
                        }
                    }
                } else {
                    $this->warn('⚠️ Could not retrieve boards. Status: ' . $boardsResponse->status());
                }
                
                return 0;
            }
            
            $this->error('❌ Connection failed with status code: ' . $response->status());
            $this->error('Response body: ' . $response->body());
            
            // Try to parse error details
            $error = json_decode($response->body(), true);
            if ($error && isset($error['message'])) {
                $this->error('Error message: ' . $error['message']);
            }
            
            return 1;
        } catch (\Exception $e) {
            $this->error('❌ Exception occurred: ' . $e->getMessage());
            return 1;
        }
    }
} 