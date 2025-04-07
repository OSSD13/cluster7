<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\TrelloService;
use Illuminate\Support\Facades\Log;

class TrelloTeamController extends Controller
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
        // Only require authentication, not admin privileges
        $this->middleware('auth');
        // Apply admin middleware only for refresh method
        $this->middleware(\App\Http\Middleware\AdminMiddleware::class)->only(['refresh']);
        $this->trelloService = $trelloService;
    }
    
    /**
     * Display the list of Trello teams (boards).
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        if (!$this->trelloService->hasValidCredentials()) {
            // Use direct routing instead of redirect
            $apiKey = \App\Models\Setting::where('key', 'trello_api_key')->first();
            $apiToken = \App\Models\Setting::where('key', 'trello_api_token')->first();
            $boardId = \App\Models\Setting::where('key', 'trello_board_id')->first();
            
            $connectionStatus = [
                'success' => false,
                'message' => 'Not tested',
                'details' => []
            ];
            
            return view('trello.settings', [
                'trelloApiKey' => $apiKey ? $apiKey->value : null,
                'trelloApiToken' => $apiToken ? $apiToken->value : null,
                'boardId' => $boardId ? $boardId->value : null,
                'connectionStatus' => $connectionStatus,
                'error' => 'Trello API credentials are not configured. Please set them up first.'
            ]);
        }
        
        // Test connection before proceeding
        $connectionTest = $this->trelloService->testConnection();
        if (!$connectionTest['success']) {
            \Log::error('Trello API connection test failed', ['details' => $connectionTest]);
            
            // Use direct routing instead of redirect
            $apiKey = \App\Models\Setting::where('key', 'trello_api_key')->first();
            $apiToken = \App\Models\Setting::where('key', 'trello_api_token')->first();
            $boardId = \App\Models\Setting::where('key', 'trello_board_id')->first();
            
            $connectionStatus = [
                'success' => false,
                'message' => $connectionTest['message'],
                'details' => $connectionTest
            ];
            
            return view('trello.settings', [
                'trelloApiKey' => $apiKey ? $apiKey->value : null,
                'trelloApiToken' => $apiToken ? $apiToken->value : null,
                'boardId' => $boardId ? $boardId->value : null,
                'connectionStatus' => $connectionStatus,
                'error' => 'Failed to connect to Trello API: ' . $connectionTest['message']
            ]);
        }
        
        try {
            // Fetch organizations (workspaces) with caching
            $organizations = $this->trelloService->getOrganizations();
            
            // Fetch all boards (teams) with members
            $options = [
                'members' => true,
                'member_fields' => 'fullName,username,avatarHash'
            ];
            
            $teams = $this->trelloService->getBoards(
                ['name', 'desc', 'url', 'idOrganization', 'closed', 'shortUrl'],
                $options
            );
            
            // Process each team (board) to get member details and registration status
            foreach ($teams as &$team) {
                // Get board members
                $teamMembers = [];
                $trelloMembers = $this->trelloService->getBoardMembers($team['id']);
                
                // Process each member
                foreach ($trelloMembers as $trelloMember) {
                    // Skip this specific user - don't add to the members list at all
                    if ($trelloMember['fullName'] === 'มกุล ๗') {
                        continue;
                    }
                    
                    $member = [
                        'id' => $trelloMember['id'],
                        'fullName' => $trelloMember['fullName'],
                        'username' => $trelloMember['username'],
                        'avatarUrl' => isset($trelloMember['avatarHash']) ? 
                            $this->trelloService->getAvatarUrl($trelloMember['id'], $trelloMember['avatarHash']) : null,
                        'email' => null,
                        'isRegistered' => false
                    ];
                    
                    // Try to get email with caching
                    $member['email'] = $this->trelloService->getMemberEmail($trelloMember['id']);
                    
                    // Check if member exists in our system (by name)
                    $systemUser = User::where('name', $trelloMember['fullName'])->first();
                    if ($systemUser) {
                        $member['isRegistered'] = true;
                        $member['email'] = $systemUser->email; // Use system email if user is registered
                        $member['role'] = $systemUser->role; // Add the user's role from database
                    }
                    
                    $teamMembers[] = $member;
                }
                
                // Add members to team
                $team['members'] = $teamMembers;
                
                // If the team belongs to an organization, get the organization details
                if (isset($team['idOrganization']) && !empty($team['idOrganization'])) {
                    foreach ($organizations as $org) {
                        if ($org['id'] == $team['idOrganization']) {
                            $team['organization'] = [
                                'id' => $org['id'],
                                'name' => $org['name'],
                                'displayName' => $org['displayName'] ?? $org['name'],
                                'logoHash' => $org['logoHash'] ?? null
                            ];
                            break;
                        }
                    }
                }
            }
            
            // For non-admin users, filter the teams to show only the ones they are a member of
            if (!auth()->user()->isAdmin()) {
                $currentUserName = auth()->user()->name;
                $teams = array_filter($teams, function($team) use ($currentUserName) {
                    // Check if the current user is a member of this team
                    foreach ($team['members'] as $member) {
                        if ($member['fullName'] === $currentUserName) {
                            return true;
                        }
                    }
                    return false;
                });
                
                // For regular users, only show their primary team (first matching team)
                // This prevents multiple teams being shown even if user is a member of multiple teams
                if (count($teams) > 1) {
                    $teams = array_slice($teams, 0, 1);
                }
            }
            return view('trello.teams.index', compact('teams', 'organizations'));
            
        } catch (\Exception $e) {
            Log::error('Error connecting to Trello API: ' . $e->getMessage());
            
            // Use direct routing instead of redirect
            $apiKey = \App\Models\Setting::where('key', 'trello_api_key')->first();
            $apiToken = \App\Models\Setting::where('key', 'trello_api_token')->first();
            $boardId = \App\Models\Setting::where('key', 'trello_board_id')->first();
            
            $connectionStatus = [
                'success' => false,
                'message' => 'Connection error',
                'details' => ['error' => $e->getMessage()]
            ];
            
            return view('trello.settings', [
                'trelloApiKey' => $apiKey ? $apiKey->value : null,
                'trelloApiToken' => $apiToken ? $apiToken->value : null,
                'boardId' => $boardId ? $boardId->value : null,
                'connectionStatus' => $connectionStatus,
                'error' => 'Error connecting to Trello API: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Display the members of a specific Trello team/organization.
     *
     * @param  string  $organizationId
     * @return \Illuminate\View\View
     */
    public function show($organizationId)
    {
        if (!$this->trelloService->hasValidCredentials()) {
            // Use direct routing instead of redirect
            $apiKey = \App\Models\Setting::where('key', 'trello_api_key')->first();
            $apiToken = \App\Models\Setting::where('key', 'trello_api_token')->first();
            $boardId = \App\Models\Setting::where('key', 'trello_board_id')->first();
            
            $connectionStatus = [
                'success' => false,
                'message' => 'Not tested',
                'details' => []
            ];
            
            return view('trello.settings', [
                'trelloApiKey' => $apiKey ? $apiKey->value : null,
                'trelloApiToken' => $apiToken ? $apiToken->value : null,
                'boardId' => $boardId ? $boardId->value : null,
                'connectionStatus' => $connectionStatus,
                'error' => 'Trello API credentials are not configured. Please set them up first.'
            ]);
        }
        
        try {
            // Fetch organization details with caching
            $organization = $this->trelloService->getOrganization($organizationId);
            
            if (!$organization) {
                // Use direct routing instead of redirect
                $organizations = $this->trelloService->getOrganizations();
                $teams = $this->trelloService->getBoards(
                    ['name', 'desc', 'url', 'idOrganization', 'closed', 'shortUrl'],
                    ['members' => true, 'member_fields' => 'fullName,username,avatarHash']
                );
                
                return view('trello.teams.index', [
                    'teams' => $teams,
                    'organizations' => $organizations,
                    'error' => 'Failed to fetch team details'
                ]);
            }
            
            // Fetch members of the organization with caching
            $trelloMembers = $this->trelloService->getOrganizationMembers($organizationId);
            $members = [];
            
            // Process each member
            foreach ($trelloMembers as $trelloMember) {
                // Skip this specific user - don't add to the members list at all
                if ($trelloMember['fullName'] === 'มกุล ๗') {
                    continue;
                }
                
                $member = [
                    'id' => $trelloMember['id'],
                    'fullName' => $trelloMember['fullName'],
                    'username' => $trelloMember['username'],
                    'avatarUrl' => isset($trelloMember['avatarHash']) ? 
                        $this->trelloService->getAvatarUrl($trelloMember['id'], $trelloMember['avatarHash']) : null,
                    'email' => null,
                    'isRegistered' => false
                ];
                
                // Try to get email with caching
                $member['email'] = $this->trelloService->getMemberEmail($trelloMember['id']);
                
                // Check if member exists in our system (by name)
                $systemUser = User::where('name', $trelloMember['fullName'])->first();
                if ($systemUser) {
                    $member['isRegistered'] = true;
                    $member['email'] = $systemUser->email; // Use system email if user is registered
                    $member['role'] = $systemUser->role; // Add the user's role from database
                }
                
                $members[] = $member;
            }
            
            // Get boards in this organization with caching
            $boards = $this->trelloService->getBoards(['name', 'desc', 'url', 'closed'], [
                'filter' => 'open'
            ]);
            
            // Filter boards to only include those in this organization
            $boards = array_filter($boards, function($board) use ($organizationId) {
                return isset($board['idOrganization']) && $board['idOrganization'] === $organizationId;
            });
            
            return view('trello.teams.show', compact('organization', 'members', 'boards'));
            
        } catch (\Exception $e) {
            Log::error("Error processing Trello team data: " . $e->getMessage());
            
            // Use direct routing instead of redirect
            $organizations = $this->trelloService->getOrganizations();
            $teams = $this->trelloService->getBoards(
                ['name', 'desc', 'url', 'idOrganization', 'closed', 'shortUrl'],
                ['members' => true, 'member_fields' => 'fullName,username,avatarHash']
            );
            
            return view('trello.teams.index', [
                'teams' => $teams,
                'organizations' => $organizations,
                'error' => 'Error processing Trello team data: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * View a specific Trello board and its members
     *
     * @param string $boardId
     * @return \Illuminate\View\View
     */
    public function viewBoard($boardId)
    {
        if (!$this->trelloService->hasValidCredentials()) {
            // Use direct routing instead of redirect
            $apiKey = \App\Models\Setting::where('key', 'trello_api_key')->first();
            $apiToken = \App\Models\Setting::where('key', 'trello_api_token')->first();
            $boardId = \App\Models\Setting::where('key', 'trello_board_id')->first();
            
            $connectionStatus = [
                'success' => false,
                'message' => 'Not tested',
                'details' => []
            ];
            
            return view('trello.settings', [
                'trelloApiKey' => $apiKey ? $apiKey->value : null,
                'trelloApiToken' => $apiToken ? $apiToken->value : null,
                'boardId' => $boardId ? $boardId->value : null,
                'connectionStatus' => $connectionStatus,
                'error' => 'Trello API credentials are not configured. Please set them up first.'
            ]);
        }
        
        try {
            // Fetch board details with caching
            $board = $this->trelloService->getBoard($boardId);
            
            if (!$board) {
                // Use direct routing instead of redirect
                $organizations = $this->trelloService->getOrganizations();
                $teams = $this->trelloService->getBoards(
                    ['name', 'desc', 'url', 'idOrganization', 'closed', 'shortUrl'],
                    ['members' => true, 'member_fields' => 'fullName,username,avatarHash']
                );
                
                return view('trello.teams.index', [
                    'teams' => $teams,
                    'organizations' => $organizations,
                    'error' => 'Failed to fetch board details'
                ]);
            }
            
            // Fetch members of the board with caching
            $trelloMembers = $this->trelloService->getBoardMembers($boardId);
            $members = [];
            
            // Process each member
            foreach ($trelloMembers as $trelloMember) {
                // Skip this specific user - don't add to the members list at all
                if ($trelloMember['fullName'] === 'มกุล ๗') {
                    continue;
                }
                
                $member = [
                    'id' => $trelloMember['id'],
                    'fullName' => $trelloMember['fullName'],
                    'username' => $trelloMember['username'],
                    'avatarUrl' => isset($trelloMember['avatarHash']) ? 
                        $this->trelloService->getAvatarUrl($trelloMember['id'], $trelloMember['avatarHash']) : null,
                    'email' => null,
                    'isRegistered' => false
                ];
                
                // Try to get email with caching
                $member['email'] = $this->trelloService->getMemberEmail($trelloMember['id']);
                
                // Check if member exists in our system (by name)
                $systemUser = User::where('name', $trelloMember['fullName'])->first();
                if ($systemUser) {
                    $member['isRegistered'] = true;
                    $member['email'] = $systemUser->email; // Use system email if user is registered
                    $member['role'] = $systemUser->role; // Add the user's role from database
                }
                
                $members[] = $member;
            }
            
            // If the board belongs to an organization, get the organization details
            $organization = null;
            if (isset($board['idOrganization']) && !empty($board['idOrganization'])) {
                $organization = $this->trelloService->getOrganization($board['idOrganization'], ['name', 'displayName']);
            }
            
            return view('trello.teams.board', compact('board', 'members', 'organization'));
            
        } catch (\Exception $e) {
            Log::error("Error processing Trello board data: " . $e->getMessage());
            
            // Use direct routing instead of redirect
            $organizations = $this->trelloService->getOrganizations();
            $teams = $this->trelloService->getBoards(
                ['name', 'desc', 'url', 'idOrganization', 'closed', 'shortUrl'],
                ['members' => true, 'member_fields' => 'fullName,username,avatarHash']
            );
            
            return view('trello.teams.index', [
                'teams' => $teams,
                'organizations' => $organizations,
                'error' => 'Error processing Trello board data: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Display all boards the user has access to
     *
     * @return \Illuminate\View\View
     */
    public function allBoards()
    {
        if (!$this->trelloService->hasValidCredentials()) {
            // Use direct routing instead of redirect
            $apiKey = \App\Models\Setting::where('key', 'trello_api_key')->first();
            $apiToken = \App\Models\Setting::where('key', 'trello_api_token')->first();
            $boardId = \App\Models\Setting::where('key', 'trello_board_id')->first();
            
            $connectionStatus = [
                'success' => false,
                'message' => 'Not tested',
                'details' => []
            ];
            
            return view('trello.settings', [
                'trelloApiKey' => $apiKey ? $apiKey->value : null,
                'trelloApiToken' => $apiToken ? $apiToken->value : null,
                'boardId' => $boardId ? $boardId->value : null,
                'connectionStatus' => $connectionStatus,
                'error' => 'Trello API credentials are not configured. Please set them up first.'
            ]);
        }
        try {
            // Fetch all boards with organization data and proper caching
            $boards = $this->trelloService->getBoards(
                ['name', 'desc', 'url', 'idOrganization', 'closed', 'shortUrl'],
                [
                    'filter' => 'open',
                    'organization' => true,
                    'organization_fields' => 'name,displayName'
                ]
            );
            
            return view('trello.teams.boards', compact('boards'));
        } catch (\Exception $e) {
            Log::error('Error connecting to Trello API: ' . $e->getMessage());
            
            // Use direct routing instead of redirect
            $apiKey = \App\Models\Setting::where('key', 'trello_api_key')->first();
            $apiToken = \App\Models\Setting::where('key', 'trello_api_token')->first();
            $boardId = \App\Models\Setting::where('key', 'trello_board_id')->first();
            
            $connectionStatus = [
                'success' => false,
                'message' => 'Connection error',
                'details' => ['error' => $e->getMessage()]
            ];
            
            return view('trello.settings', [
                'trelloApiKey' => $apiKey ? $apiKey->value : null,
                'trelloApiToken' => $apiToken ? $apiToken->value : null,
                'boardId' => $boardId ? $boardId->value : null,
                'connectionStatus' => $connectionStatus,
                'error' => 'Error connecting to Trello API: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Refresh Trello teams data
     *
     * @return \Illuminate\View\View
     */
    public function refresh()
    {
        if (!$this->trelloService->hasValidCredentials()) {
            // Use direct routing instead of redirect
            $apiKey = \App\Models\Setting::where('key', 'trello_api_key')->first();
            $apiToken = \App\Models\Setting::where('key', 'trello_api_token')->first();
            $boardId = \App\Models\Setting::where('key', 'trello_board_id')->first();
            
            $connectionStatus = [
                'success' => false,
                'message' => 'Not tested',
                'details' => []
            ];
            
            return view('trello.settings', [
                'trelloApiKey' => $apiKey ? $apiKey->value : null,
                'trelloApiToken' => $apiToken ? $apiToken->value : null,
                'boardId' => $boardId ? $boardId->value : null,
                'connectionStatus' => $connectionStatus,
                'error' => 'Trello API credentials are not configured. Please set them up first.'
            ]);
        }
        
        try {
            // Clear cache for Trello data to force fresh retrieval
            $this->trelloService->clearCache();
            
            // Use direct routing instead of redirect
            $organizations = $this->trelloService->getOrganizations();
            
            $options = [
                'members' => true,
                'member_fields' => 'fullName,username,avatarHash'
            ];
            
            $teams = $this->trelloService->getBoards(
                ['name', 'desc', 'url', 'idOrganization', 'closed', 'shortUrl'],
                $options
            );
            
            return view('trello.teams.index', [
                'teams' => $teams,
                'organizations' => $organizations,
                'success' => 'Trello team data refreshed successfully.'
            ]);
                
        } catch (\Exception $e) {
            Log::error('Error refreshing Trello data: ' . $e->getMessage());
            
            // Use direct routing instead of redirect
            $organizations = $this->trelloService->getOrganizations();
            $teams = $this->trelloService->getBoards(
                ['name', 'desc', 'url', 'idOrganization', 'closed', 'shortUrl'],
                ['members' => true, 'member_fields' => 'fullName,username,avatarHash']
            );
            
            return view('trello.teams.index', [
                'teams' => $teams,
                'organizations' => $organizations,
                'error' => 'Error refreshing Trello data: ' . $e->getMessage()
            ]);
        }
    }
}