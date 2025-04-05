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
                ])->get('https://api.trello.com/1/members/me', [
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
                    $connectionStatus = [
                        'success' => false,
                        'message' => 'Failed to connect to Trello API. Please check your credentials.'
                    ];
                }
            } catch (\Exception $e) {
                $connectionStatus = [
                    'success' => false,
                    'message' => 'Error connecting to Trello API: ' . $e->getMessage()
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
     * @return \Illuminate\Http\RedirectResponse
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

        return redirect()->route('trello.settings.index')->with('success', 'Trello API settings updated successfully.');
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

            // Log the test connection attempt
            \Log::info('Testing Trello API connection', [
                'endpoint' => 'https://api.trello.com/1/members/me'
            ]);

            $response = Http::withOptions([
                'verify' => false,  // Try disabling SSL verification if needed
                'timeout' => 15,    // Set a reasonable timeout
            ])->get('https://api.trello.com/1/members/me', [
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

            // Log failure details
            \Log::warning('Trello API connection test failed', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to connect to Trello API. Status code: ' . $response->status(),
                'details' => app()->environment('local') ? $response->body() : null
            ]);
        } catch (\Exception $e) {
            \Log::error('Error testing Trello API connection', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error connecting to Trello API: ' . $e->getMessage(),
                'details' => app()->environment('local') ? $e->getTraceAsString() : null
            ]);
        }
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
