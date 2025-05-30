<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Services\TrelloService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

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
        // Apply auth middleware to all methods
        $this->middleware('auth');
        
        // Apply admin middleware only to specific methods
        $this->middleware(\App\Http\Middleware\AdminMiddleware::class)->only([
            'index', 'show', 'viewBoard', 'refresh'
        ]);
        
        $this->trelloService = $trelloService;
    }
    
    /**
     * Display the list of Trello teams (boards).
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function index()
    {
        if (!$this->trelloService->hasValidCredentials()) {
            return redirect()->route('trello.settings.index')
                ->with('error', 'Trello API credentials are not configured. Please set them up first.');
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
            
            return view('trello.teams.index', compact('teams', 'organizations'));
        } catch (\Exception $e) {
            Log::error('Error connecting to Trello API: ' . $e->getMessage());
            return redirect()->route('trello.settings.index')
                ->with('error', 'Error connecting to Trello API: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the members of a specific Trello team/organization.
     *
     * @param  string  $organizationId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function show($organizationId)
    {
        if (!$this->trelloService->hasValidCredentials()) {
            return redirect()->route('trello.settings.index')
                ->with('error', 'Trello API credentials are not configured. Please set them up first.');
        }
        
        try {
            // Fetch organization details with caching
            $organization = $this->trelloService->getOrganization($organizationId);
            
            if (!$organization) {
                return redirect()->route('trello.teams.index')
                    ->with('error', 'Failed to fetch team details');
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
            return redirect()->route('trello.teams.index')
                ->with('error', 'Error processing Trello team data: ' . $e->getMessage());
        }
    }
    
    /**
     * View a specific Trello board and its members
     *
     * @param string $boardId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function viewBoard($boardId)
    {
        if (!$this->trelloService->hasValidCredentials()) {
            return redirect()->route('trello.settings.index')
                ->with('error', 'Trello API credentials are not configured. Please set them up first.');
        }
        
        try {
            // Fetch board details with caching
            $board = $this->trelloService->getBoard($boardId);
            
            if (!$board) {
                return redirect()->route('trello.teams.index')
                    ->with('error', 'Failed to fetch board details');
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
            return redirect()->route('trello.teams.index')
                ->with('error', 'Error processing Trello board data: ' . $e->getMessage());
        }
    }
    
    /**
     * Display all boards the user has access to
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function allBoards()
    {
        if (!$this->trelloService->hasValidCredentials()) {
            return redirect()->route('trello.settings.index')
                ->with('error', 'Trello API credentials are not configured. Please set them up first.');
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
            return redirect()->route('trello.settings.index')
                ->with('error', 'Error connecting to Trello API: ' . $e->getMessage());
        }
    }
    
    /**
     * Refresh Trello teams data and redirect back to teams index
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function refresh()
    {
        if (!$this->trelloService->hasValidCredentials()) {
            return redirect()->route('trello.settings.index')
                ->with('error', 'Trello API credentials are not configured. Please set them up first.');
        }
        
        try {
            // Clear cache for Trello data to force fresh retrieval
            $this->trelloService->clearCache();
            
            return redirect()->route('trello.teams.index')
                ->with('success', 'Trello team data refreshed successfully.');
                
        } catch (\Exception $e) {
            Log::error('Error refreshing Trello data: ' . $e->getMessage());
            return redirect()->route('trello.teams.index')
                ->with('error', 'Error refreshing Trello data: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the list of Trello teams (boards) the authenticated user is a member of.
     *
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function myTeams()
    {
        if (!$this->trelloService->hasValidCredentials()) {
            return redirect()->route('dashboard')
                ->with('error', 'Trello API credentials are not configured. Please contact an administrator.');
        }
        
        try {
            // Get current user
            $user = Auth::user();
            
            // Fetch organizations (workspaces) with caching
            $organizations = $this->trelloService->getOrganizations();
            
            // Fetch all boards (teams) with members
            $options = [
                'members' => true,
                'member_fields' => 'fullName,username,avatarHash'
            ];
            
            $allTeams = $this->trelloService->getBoards(
                ['name', 'desc', 'url', 'idOrganization', 'closed', 'shortUrl'],
                $options
            );
            
            // Filter teams to only include those where the current user is a member
            $teams = [];
            
            foreach ($allTeams as $team) {
                // Skip closed boards
                if (isset($team['closed']) && $team['closed']) {
                    continue;
                }
                
                // Get board members
                $teamMembers = [];
                $trelloMembers = $this->trelloService->getBoardMembers($team['id']);
                $isUserInTeam = false;
                
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
                        
                        // Check if this member is the current user
                        if ($systemUser->id === $user->id) {
                            $isUserInTeam = true;
                        }
                    }
                    
                    $teamMembers[] = $member;
                }
                
                // Skip teams where user is not a member
                if (!$isUserInTeam) {
                    continue;
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
                
                $teams[] = $team;
            }
            
            return view('trello.teams.my-teams', compact('teams', 'organizations'));
            
        } catch (\Exception $e) {
            Log::error('Error connecting to Trello API: ' . $e->getMessage());
            return redirect()->route('dashboard')
                ->with('error', 'Error connecting to Trello API: ' . $e->getMessage());
        }
    }
    
    /**
     * Display the members of a specific Trello team/organization for the authenticated user.
     *
     * @param  string  $teamId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function viewMyTeam($teamId)
    {
        if (!$this->trelloService->hasValidCredentials()) {
            return redirect()->route('dashboard')
                ->with('error', 'Trello API credentials are not configured. Please contact an administrator.');
        }
        
        try {
            // Get current user
            $user = Auth::user();
            
            // Fetch board details with caching
            $board = $this->trelloService->getBoard($teamId);
            
            if (!$board) {
                return redirect()->route('my-teams.index')
                    ->with('error', 'Failed to fetch team details');
            }
            
            // Verify the user is a member of this board
            $boardMembers = $this->trelloService->getBoardMembers($teamId);
            $isUserInTeam = false;
            
            foreach ($boardMembers as $boardMember) {
                $systemUser = User::where('name', $boardMember['fullName'])->first();
                if ($systemUser && $systemUser->id === $user->id) {
                    $isUserInTeam = true;
                    break;
                }
            }
            
            if (!$isUserInTeam) {
                return redirect()->route('my-teams.index')
                    ->with('error', 'You do not have access to this team');
            }
            
            // Process the board members
            $members = [];
            
            foreach ($boardMembers as $trelloMember) {
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
                $organization = $this->trelloService->getOrganization($board['idOrganization']);
            }
            
            return view('trello.teams.my-team', compact('board', 'members', 'organization'));
            
        } catch (\Exception $e) {
            Log::error("Error processing Trello team data: " . $e->getMessage());
            return redirect()->route('my-teams.index')
                ->with('error', 'Error processing Trello team data: ' . $e->getMessage());
        }
    }

    /**
     * Generate a profile for a specific Trello team.
     *
     * @param  string  $teamId
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function generateTeamProfile($teamId)
    {
        if (!$this->trelloService->hasValidCredentials()) {
            return redirect()->route('dashboard')
                ->with('error', 'Trello API credentials are not configured. Please contact an administrator.');
        }
        
        try {
            // Get current user
            $user = Auth::user();
            
            // Fetch board details with caching
            $board = $this->trelloService->getBoard($teamId);
            
            if (!$board) {
                return redirect()->route('my-teams.index')
                    ->with('error', 'Failed to fetch team details');
            }
            
            // Verify the user is a member of this board
            $boardMembers = $this->trelloService->getBoardMembers($teamId);
            $isUserInTeam = false;
            $members = [];
            
            foreach ($boardMembers as $boardMember) {
                // Skip this specific user - don't add to the members list at all
                if ($boardMember['fullName'] === 'มกุล ๗') {
                    continue;
                }
                
                $member = [
                    'id' => $boardMember['id'],
                    'fullName' => $boardMember['fullName'],
                    'username' => $boardMember['username'],
                    'avatarUrl' => isset($boardMember['avatarHash']) ? 
                        $this->trelloService->getAvatarUrl($boardMember['id'], $boardMember['avatarHash']) : null,
                    'email' => null,
                    'isRegistered' => false
                ];
                
                // Try to get email with caching
                $member['email'] = $this->trelloService->getMemberEmail($boardMember['id']);
                
                // Check if member exists in our system (by name)
                $systemUser = User::where('name', $boardMember['fullName'])->first();
                if ($systemUser) {
                    $member['isRegistered'] = true;
                    $member['email'] = $systemUser->email; // Use system email if user is registered
                    $member['role'] = $systemUser->role; // Add the user's role from database
                }
                
                // Check if this member is the current user
                if ($systemUser && $systemUser->id === $user->id) {
                    $isUserInTeam = true;
                }
                
                $members[] = $member;
            }
            
            if (!$isUserInTeam) {
                return redirect()->route('my-teams.index')
                    ->with('error', 'You do not have access to this team');
            }
            
            // If the board belongs to an organization, get the organization details
            $organization = null;
            if (isset($board['idOrganization']) && !empty($board['idOrganization'])) {
                $organization = $this->trelloService->getOrganization($board['idOrganization']);
            }
            
            return view('trello.teams.team-profile', compact('board', 'members', 'organization'));
            
        } catch (\Exception $e) {
            Log::error("Error generating team profile: " . $e->getMessage());
            return redirect()->route('my-teams.index')
                ->with('error', 'Error generating team profile: ' . $e->getMessage());
        }
    }
}