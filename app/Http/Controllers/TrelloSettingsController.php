<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Setting;
use App\Services\TrelloService;

class TrelloSettingsController extends Controller
{
    /**
     * The Trello service instance.
     *
     * @var \App\Services\TrelloService
     */
    protected $trelloService;

    /**
     * Create a new controller instance.
     *
     * @param \App\Services\TrelloService $trelloService
     * @return void
     */
    public function __construct(TrelloService $trelloService)
    {
        $this->middleware(['auth', \App\Http\Middleware\AdminMiddleware::class]);
        $this->trelloService = $trelloService;
    }

    /**
     * Display the Trello settings form.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $apiKey = $this->getSetting('trello_api_key');
        $apiToken = $this->getSetting('trello_api_token');
        $boardId = $this->getSetting('trello_board_id');
        
        // Initialize connectionStatus as null
        $connectionStatus = null;
        
        // Check if credentials exist and test connection
        if ($apiKey && $apiToken) {
            try {
                $response = Http::withOptions([
                    'verify' => false,
                    'timeout' => 15,
                ])->get($this->getTrelloApiBaseUrl() . 'members/me', [
                    'key' => $apiKey,
                    'token' => $apiToken,
                    'boards' => 'open',
                    'board_fields' => 'name,url',
                    'board_limit' => 5
                ]);
                
                if ($response->successful()) {
                    $user = $response->json();
                    $connectionStatus = [
                        'success' => true,
                        'message' => 'Connected to Trello API',
                        'fullName' => $user['fullName'] ?? 'User',
                        'username' => $user['username'] ?? '',
                        'boards' => array_slice($user['boards'] ?? [], 0, 5)
                    ];
                } else {
                    // Parse response body for more details
                    $errorBody = $response->body();
                    $errorJson = json_decode($errorBody, true);
                    $errorMessage = $errorJson && isset($errorJson['message']) ? $errorJson['message'] : $errorBody;
                    
                    // Log the error
                    \Log::warning('Trello API connection test failed on settings page', [
                        'status' => $response->status(),
                        'body' => $errorBody,
                        'key_length' => strlen($apiKey),
                        'token_length' => strlen($apiToken)
                    ]);
                    
                    $connectionStatus = [
                        'success' => false,
                        'message' => 'Failed to connect to Trello API: ' . $errorMessage,
                        'details' => [
                            'status' => $response->status(),
                            'response' => $errorJson ?? $errorBody
                        ]
                    ];
                }
            } catch (\Exception $e) {
                \Log::error('Exception connecting to Trello API', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                $connectionStatus = [
                    'success' => false,
                    'message' => 'Error connecting to Trello API: ' . $e->getMessage(),
                    'details' => [
                        'exception' => get_class($e),
                        'message' => $e->getMessage()
                    ]
                ];
            }
        }

        return view('trello.settings', [
            'trelloApiKey' => $apiKey,
            'trelloApiToken' => $apiToken,
            'boardId' => $boardId,
            'connectionStatus' => $connectionStatus
        ]);
    }

    /**
     * Update the Trello API settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function update(Request $request)
    {
        $request->validate([
            'trello_api_key' => 'required|string',
            'trello_api_token' => 'required|string',
            'board_id' => 'nullable|string'
        ]);

        // Save the settings to the database
        $this->saveSetting('trello_api_key', $request->trello_api_key);
        $this->saveSetting('trello_api_token', $request->trello_api_token);
        
        // Only save board_id if it's present
        if ($request->has('board_id')) {
            $this->saveSetting('trello_board_id', $request->board_id);
        }

        // Use direct routing instead of redirect
        $apiKey = $this->getSetting('trello_api_key');
        $apiToken = $this->getSetting('trello_api_token');
        $boardId = $this->getSetting('trello_board_id');
        
        $connectionStatus = [
            'success' => false,
            'message' => 'Not tested',
            'details' => []
        ];
        
        return view('trello.settings', [
            'trelloApiKey' => $apiKey,
            'trelloApiToken' => $apiToken,
            'boardId' => $boardId,
            'connectionStatus' => $connectionStatus,
            'success' => 'Trello API settings updated successfully.'
        ]);
    }

    /**
     * Test the API connection via AJAX
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function testApiConnection(Request $request)
    {
        try {
            $apiKey = $request->input('trello_api_key') ?: $this->getSetting('trello_api_key');
            $apiToken = $request->input('trello_api_token') ?: $this->getSetting('trello_api_token');

            if (!$apiKey || !$apiToken) {
                return response()->json(['success' => false, 'message' => 'API credentials are not configured.']);
            }

            // Log the test connection attempt with more details
            \Log::info('Testing Trello API connection', [
                'endpoint' => $this->getTrelloApiBaseUrl() . 'members/me',
                'key_length' => strlen($apiKey),
                'token_length' => strlen($apiToken)
            ]);

            $response = Http::withOptions([
                'verify' => false,  // Try disabling SSL verification if needed
                'timeout' => 15,    // Set a reasonable timeout
            ])->get($this->getTrelloApiBaseUrl() . 'members/me', [
                'key' => $apiKey,
                'token' => $apiToken,
                'boards' => 'open',
                'board_fields' => 'name,url',
                'board_limit' => 5
            ]);

            if ($response->successful()) {
                $user = $response->json();
                $boards = array_slice($user['boards'] ?? [], 0, 5);
                
                return response()->json([
                    'success' => true,
                    'message' => 'Successfully connected to Trello API. Welcome, ' . ($user['fullName'] ?? 'User') . '!',
                    'fullName' => $user['fullName'] ?? 'User',
                    'username' => $user['username'] ?? '',
                    'boards' => $boards
                ]);
            }

            // Log failure details with more information
            \Log::warning('Trello API connection test failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'headers' => $response->headers()
            ]);

            // Provide more detailed error message
            $errorBody = $response->body();
            $errorJson = json_decode($errorBody, true);
            $errorMessage = $errorJson && isset($errorJson['message']) ? $errorJson['message'] : $errorBody;

            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to Trello API. Status code: ' . $response->status() . 
                             '. Error: ' . $errorMessage,
                'details' => $errorJson ?? $errorBody
            ]);
        } catch (\Exception $e) {
            \Log::error('Error testing Trello API connection', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error connecting to Trello API: ' . $e->getMessage(),
                'details' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get the Trello API base URL.
     *
     * @return string
     */
    private function getTrelloApiBaseUrl()
    {
        return $this->trelloService->getBaseUrl();
    }

    private function getSetting($key)
    {
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : null;
    }

    private function saveSetting($key, $value)
    {
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
}
