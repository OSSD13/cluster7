<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\MinorCaseService;
use App\Services\TrelloService;
use App\Models\Sprint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

final class MinorCasesController extends Controller
{
    /**
     * The Trello service instance.
     */
    private TrelloService $trelloService;
    
    /**
     * The Minor Case service instance.
     */
    private MinorCaseService $minorCaseService;

    /**
     * Create a new controller instance.
     *
     * @param TrelloService $trelloService
     * @param MinorCaseService $minorCaseService
     */
    public function __construct(TrelloService $trelloService, MinorCaseService $minorCaseService)
    {
        $this->middleware('auth');
        $this->trelloService = $trelloService;
        $this->minorCaseService = $minorCaseService;
    }

    /**
     * Display the minor cases page with user's team data
     */
    public function index(): View
    {
        // Get list of available sprints for the dropdown
        $sprints = Sprint::orderBy('sprint_number', 'desc')->get();
        
        // Get the current year for the view
        $currentYear = date('Y');
        
        // Get all minor cases for the current user
        $minorCases = \App\Models\MinorCase::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('minorcases', compact('sprints', 'currentYear', 'minorCases'));
    }

    /**
     * Get minor cases data filtered by user's team if needed
     */
    public function getMinorCasesData(Request $request): JsonResponse
    {
        try {
            // Get the boards the user has access to
            $userBoards = $this->getUserBoards();
            
            // Prepare data by board
            $minorCasesData = [];
            
            foreach ($userBoards as $board) {
                // Get minor cases for this board
                $boardMinorCases = $this->minorCaseService->getByBoard($board['id'], auth()->id());
                
                // Map to include the board name
                foreach ($boardMinorCases as $minorCase) {
                    $minorCasesData[] = [
                        'id' => $minorCase->id,
                        'board_id' => $minorCase->board_id,
                        'board_name' => $board['name'] ?? 'Unknown Board',
                        'sprint_number' => $minorCase->sprint,
                        'card' => $minorCase->card,
                        'description' => $minorCase->description,
                        'member' => $minorCase->member,
                        'points' => (float) $minorCase->points,
                        'created_at' => $minorCase->created_at->format('Y-m-d H:i:s'),
                    ];
                }
            }
            
            return response()->json($minorCasesData);
            
        } catch (\Exception $e) {
            Log::error('Error fetching minor cases data: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to fetch minor cases data',
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get the boards accessible to the current user
     *
     * @return array Array of board data (id and name)
     */
    private function getUserBoards(): array
    {
        $userBoards = [];

        if ($this->trelloService->hasValidCredentials()) {
            try {
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
                            if (isset($member['fullName']) && $member['fullName'] === auth()->user()->name) {
                                $userBoards[] = [
                                    'id' => $board['id'],
                                    'name' => $board['name']
                                ];
                                break;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                // Log error but don't fail - empty board list will result
                Log::error('Error fetching user boards: ' . $e->getMessage());
            }
        }
        
        return $userBoards;
    }
} 