<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SprintReport;
use App\Models\Sprint;
use App\Helpers\DateHelper;
use Illuminate\Support\Facades\DB;
use App\Models\SavedReport;
use App\Services\TrelloService;

class BacklogController extends Controller
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
     * Display a listing of all backlog bugs from past sprints.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $reports = SavedReport::with('sprint')->get();
        
        // Initialize collections for backlog bugs
        $allBacklogBugs = collect();
        $backlogByTeam = [];
        $backlogBySprint = [];
        
        // First pass: collect all bugs from reports (both current bugs and backlog)
        foreach ($reports as $report) {
            // Handle cases where report_data is already decoded or is a string
            $reportData = $report->report_data;
            if (is_string($reportData)) {
                $reportData = json_decode($reportData, true);
            }
            
            // Process current bug cards in this report
            if (isset($reportData['bug_cards']) && is_array($reportData['bug_cards'])) {
                foreach ($reportData['bug_cards'] as $teamName => $bugs) {
                    foreach ($bugs as $bug) {
                        // Only include active bugs (not completed)
                        if (!isset($bug['status']) || $bug['status'] !== 'completed') {
                            $bug['team'] = $teamName;
                            $bug['sprint_number'] = $report->sprint->sprint_number;
                            $allBacklogBugs->put($bug['id'], $bug);
                        }
                    }
                }
            }
            
            // Process backlog data in this report
            if (isset($reportData['backlog']) && is_array($reportData['backlog'])) {
                foreach ($reportData['backlog'] as $teamName => $bugs) {
                    foreach ($bugs as $bug) {
                        // Only include active bugs (not completed)
                        if (!isset($bug['status']) || $bug['status'] !== 'completed') {
                            $bug['team'] = $teamName;
                            $bug['sprint_number'] = $report->sprint->sprint_number;
                            $allBacklogBugs->put($bug['id'], $bug);
                        }
                    }
                }
            }
        }

        // If user is not admin, filter to show only their team's bugs
        if (!auth()->user()->isAdmin()) {
            $userTeams = $this->getUserTeams();
            $allBacklogBugs = $allBacklogBugs->filter(function ($bug) use ($userTeams) {
                return in_array($bug['team'], $userTeams);
            });
        }
        
        // Second pass: organize bugs by team and sprint
        foreach ($allBacklogBugs as $bug) {
            $teamName = $bug['team'];
            $sprintNumber = $bug['sprint_number'];
            
            // Organize by team
            if (!isset($backlogByTeam[$teamName])) {
                $backlogByTeam[$teamName] = collect();
            }
            $backlogByTeam[$teamName]->push($bug);
            
            // Organize by sprint
            if (!isset($backlogBySprint[$sprintNumber])) {
                $backlogBySprint[$sprintNumber] = collect();
            }
            $backlogBySprint[$sprintNumber]->push($bug);
        }
        
        // Sort bugs by priority
        $sortedBacklogBugs = $allBacklogBugs->sortBy(function ($bug) {
            $priority = $this->getBugPriority($bug);
            return $this->getPriorityValue($priority);
        });
        
        // Sort sprints by number
        $backlogBySprint = collect($backlogBySprint)->sortKeys();
        
        return view('backlog.index', [
            'allBugs' => $sortedBacklogBugs,
            'bugsByTeam' => $backlogByTeam,
            'bugsBySprint' => $backlogBySprint
        ]);
    }

    /**
     * Get the backlog data for use in other controllers.
     * 
     * @return array
     */
    public function getBacklogData()
    {
        $reports = SavedReport::with('sprint')->get();
        
        // Initialize collections for backlog bugs
        $allBacklogBugs = collect();
        $backlogByTeam = [];
        $backlogBySprint = [];
        
        // First pass: collect all bugs from reports (both current bugs and backlog)
        foreach ($reports as $report) {
            // Handle cases where report_data is already decoded or is a string
            $reportData = $report->report_data;
            if (is_string($reportData)) {
                $reportData = json_decode($reportData, true);
            }
            
            // Process current bug cards in this report
            if (isset($reportData['bug_cards']) && is_array($reportData['bug_cards'])) {
                foreach ($reportData['bug_cards'] as $teamName => $bugs) {
                    foreach ($bugs as $bug) {
                        // Only include active bugs (not completed)
                        if (!isset($bug['status']) || $bug['status'] !== 'completed') {
                            $bug['team'] = $teamName;
                            $bug['sprint_number'] = $report->sprint->sprint_number;
                            $allBacklogBugs->put($bug['id'], $bug);
                        }
                    }
                }
            }
            
            // Process backlog data in this report
            if (isset($reportData['backlog']) && is_array($reportData['backlog'])) {
                foreach ($reportData['backlog'] as $teamName => $bugs) {
                    foreach ($bugs as $bug) {
                        // Only include active bugs (not completed)
                        if (!isset($bug['status']) || $bug['status'] !== 'completed') {
                            $bug['team'] = $teamName;
                            $bug['sprint_number'] = $report->sprint->sprint_number;
                            $allBacklogBugs->put($bug['id'], $bug);
                        }
                    }
                }
            }
        }
        
        // If user is not admin, filter to show only their team's bugs
        if (!auth()->user()->isAdmin()) {
            $userTeams = $this->getUserTeams();
            $allBacklogBugs = $allBacklogBugs->filter(function ($bug) use ($userTeams) {
                return in_array($bug['team'], $userTeams);
            });
        }
        
        // Second pass: organize bugs by team and sprint
        foreach ($allBacklogBugs as $bug) {
            $teamName = $bug['team'];
            $sprintNumber = $bug['sprint_number'];
            
            // Organize by team
            if (!isset($backlogByTeam[$teamName])) {
                $backlogByTeam[$teamName] = collect();
            }
            $backlogByTeam[$teamName]->push($bug);
            
            // Organize by sprint
            if (!isset($backlogBySprint[$sprintNumber])) {
                $backlogBySprint[$sprintNumber] = collect();
            }
            $backlogBySprint[$sprintNumber]->push($bug);
        }
        
        // Sort bugs by priority
        $sortedBacklogBugs = $allBacklogBugs->sortBy(function ($bug) {
            $priority = $this->getBugPriority($bug);
            return $this->getPriorityValue($priority);
        });
        
        // Sort sprints by number
        $backlogBySprint = collect($backlogBySprint)->sortKeys();
        
        return [
            'allBugs' => $sortedBacklogBugs,
            'bugsByTeam' => $backlogByTeam,
            'bugsBySprint' => $backlogBySprint,
            'bugCount' => $sortedBacklogBugs->count() . ' ' . \Illuminate\Support\Str::plural('bug', $sortedBacklogBugs->count()),
            'totalBugPoints' => $sortedBacklogBugs->sum('points')
        ];
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
                
                // Filter for boards where user is a direct member (not workspace-level access)
                foreach ($boards as $board) {
                    if (isset($board['members']) && is_array($board['members'])) {
                        foreach ($board['members'] as $member) {
                            if (isset($member['fullName']) && $member['fullName'] === $userName) {
                                // User is explicitly added to this board
                                $userTeams[] = $board['name'];
                                break;
                            }
                        }
                    }
                }
                
                // Allow users to see backlogs from all their teams
                // Removed the limitation to only see the first team
            } catch (\Exception $e) {
                // Log error but don't fail - empty team list will result
                \Log::error('Error fetching user teams: ' . $e->getMessage());
            }
        }
        
        return $userTeams;
    }

    private function getBugPriority($bug)
    {
        // Check if the bug has labels
        if (isset($bug['labels']) && is_array($bug['labels'])) {
            // Look for priority labels: High, Medium, Low
            foreach ($bug['labels'] as $label) {
                if (in_array($label, ['High', 'Medium', 'Low'])) {
                    return $label;
                }
            }
        }
        
        // Default to Low priority if no priority label found
        return 'Low';
    }

    private function getPriorityValue($priority)
    {
        // Return numeric values for sorting (lower value = higher priority)
        switch ($priority) {
            case 'High':
                return 1;
            case 'Medium':
                return 2;
            case 'Low':
            default:
                return 3;
        }
    }

    /**
     * Remove a bug from the backlog.
     *
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            // Get all reports
            $reports = SavedReport::with('sprint')->get();
            $bugFound = false;

            // Iterate through each report to find and remove the bug
            foreach ($reports as $report) {
                $reportData = $report->report_data;
                if (is_string($reportData)) {
                    $reportData = json_decode($reportData, true);
                }
                $modified = false;
                // Check in bug_cards section
                if (isset($reportData['bug_cards']) && is_array($reportData['bug_cards'])) {
                    foreach ($reportData['bug_cards'] as $teamName => &$bugs) {
                        foreach ($bugs as $key => $bug) {
                            if (isset($bug['id']) && $bug['id'] === $id) {
                                unset($bugs[$key]);
                                $modified = true;
                                $bugFound = true;
                            }
                        }
                        // Reindex array after removal
                        $bugs = array_values($bugs);
                    }
                }
                // Check in backlog section
                if (isset($reportData['backlog']) && is_array($reportData['backlog'])) {
                    foreach ($reportData['backlog'] as $teamName => &$bugs) {
                        foreach ($bugs as $key => $bug) {
                            if (isset($bug['id']) && $bug['id'] === $id) {
                                unset($bugs[$key]);
                                $modified = true;
                                $bugFound = true;
                            }
                        }
                        // Reindex array after removal
                        $bugs = array_values($bugs);
                    }
                }

                // If the report was modified, save it
                if ($modified) {
                    $report->report_data = $reportData;
                    $report->save();
                }
            }

            if ($bugFound) {
                return redirect()->route('backlog.index')->with('success', 'Bug deleted successfully');
            } else {
                return redirect()->route('backlog.index')->with('error', 'Bug not found');
            }
        } catch (\Exception $e) {
            return redirect()->route('backlog.index')->with('error', 'Error deleting bug: ' . $e->getMessage());
        }
    }
}
