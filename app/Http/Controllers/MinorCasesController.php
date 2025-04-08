<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TrelloService;
use Illuminate\Support\Facades\Log;

class MinorCasesController extends Controller
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
        $this->middleware('auth');
        $this->trelloService = $trelloService;
    }

    /**
     * Display the minor cases page with user's team data
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // For now, just return the view
        // In the future, we can filter minor cases data based on user teams
        return view('minorcases');
    }

    /**
     * Get minor cases data filtered by user's team if needed
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getMinorCasesData()
    {
        try {
            // Get report data (this would be replaced with your actual data source)
            $reportData = $this->fetchReportData();
            
            // If user is not admin, filter by their teams
            if (!auth()->user()->isAdmin()) {
                $userTeams = $this->getUserTeams();
                $reportData = array_filter($reportData, function($item) use ($userTeams) {
                    return isset($item['team']) && in_array($item['team'], $userTeams);
                });
            }
            
            return response()->json(array_values($reportData));
            
        } catch (\Exception $e) {
            Log::error('Error fetching minor cases data: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch minor cases data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Fetch report data from the database/API
     * This is a placeholder method - replace with actual data source
     *
     * @return array
     */
    private function fetchReportData()
    {
        // This would be replaced with your actual data source
        // Currently returning an empty array as placeholder
        return [];
    }

    /**
     * Get the teams the current user belongs to
     *
     * @return array Array of team names the user is a member of
     */
    protected function getUserTeams()
    {
        $userTeams = [];

        if ($this->trelloService->hasValidCredentials()) {
            try {
                // Get current user's name
                $userName = auth()->user()->name;
                
                // Get all boards with members
                $options = [
                    'members' => true,
                    'member_fields' => 'fullName'
                ];
                
                $boards = $this->trelloService->getBoards(['id', 'name'], $options);
                
                // Filter for boards where user is a member
                foreach ($boards as $board) {
                    if (isset($board['members'])) {
                        foreach ($board['members'] as $member) {
                            if (isset($member['fullName']) && $member['fullName'] === $userName) {
                                $userTeams[] = $board['name'];
                                break;
                            }
                        }
                    }
                }
                // Allow users to see data from all teams they belong to
                // Removed the limitation to first team only
            } catch (\Exception $e) {
                // Log error but don't fail - empty team list will result
                \Log::error('Error fetching user teams: ' . $e->getMessage());
            }
        }
        
        return $userTeams;
    }
} 