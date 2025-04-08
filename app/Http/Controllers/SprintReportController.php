<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sprint;
use App\Models\SprintReport;
use App\Helpers\DateHelper;
use Illuminate\Support\Facades\Auth;
use App\Services\TrelloService;

class SprintReportController extends Controller
{
    private $trelloService;

    public function __construct()
    {
        $this->middleware('auth');
        $this->trelloService = app(\App\Services\TrelloService::class);
    }

    /**
     * Display a listing of saved sprint reports.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // If user is admin, show all sprints and reports
        if (auth()->user()->isAdmin()) {
            // Get all sprints with their reports, ordered by latest sprint first
            $sprints = Sprint::with(['reports' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])
                ->orderBy('sprint_number', 'desc')
                ->get();
        } else {
            // For non-admin users, filter reports based on their team membership
            $userTeams = $this->getUserTeams();

            // Get all sprints
            $sprints = Sprint::with(['reports' => function($query) use ($userTeams) {
                // Only include reports for teams the user belongs to
                if (!empty($userTeams)) {
                    $query->whereIn('board_name', $userTeams);
                }
                $query->orderBy('created_at', 'desc');
            }])
                ->orderBy('sprint_number', 'desc')
                ->get();
                
            // Remove sprints that don't have any reports for user's teams
            $sprints = $sprints->filter(function($sprint) {
                return $sprint->reports->count() > 0;
            });
            
            \Log::info('Filtered sprint reports for user: ' . auth()->user()->name, [
                'user_teams' => $userTeams,
                'sprint_count' => $sprints->count()
            ]);
        }
            
        return view('reports.sprints.index', compact('sprints'));
    }
    
    /**
     * Display a specific sprint with all its reports.
     *
     * @param int $sprintId
     * @return \Illuminate\Http\Response
     */
    public function showSprint($sprintId)
    {
        $sprint = Sprint::with(['reports' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($sprintId);
        
        // For non-admin users, filter reports based on their team membership
        if (!auth()->user()->isAdmin()) {
            // Get the user's name
            $user = auth()->user();
            $userName = $user->full_name ?? $user->name;
            
            $userTeams = $this->getUserTeams();
            
            // Filter the reports collection to only include user's teams
            if (!empty($userTeams)) {
                $sprint->reports = $sprint->reports->filter(function($report) use ($userTeams) {
                    return in_array($report->board_name, $userTeams);
                });
            } else {
                // If user doesn't belong to any teams, they shouldn't see any reports
                $sprint->reports = collect([]);
            }
            
            // If no reports for this user's teams, redirect to my-team-reports
            if ($sprint->reports->isEmpty()) {
                return redirect()->route('my-team-reports')
                    ->with('warning', 'No reports found for your teams in Sprint #' . $sprint->sprint_number);
            }
        }
        
        // Group reports by board_name (team)
        $reportsByTeam = [];
        $reportVersions = [];
        $highestVersionsByTeam = [];
        
        // Debug information
        \Log::info('Reports for Sprint #' . $sprint->sprint_number);
        
        foreach ($sprint->reports as $report) {
            $teamName = $report->board_name ?? 'Unknown Team';
            
            \Log::info("Report ID: {$report->id}, Name: {$report->report_name}, Created: {$report->created_at}");
            
            if (!isset($reportsByTeam[$teamName])) {
                $reportsByTeam[$teamName] = [];
                $highestVersionsByTeam[$teamName] = 0;
            }
            
            // Ensure report has a valid sprint reference
            if ($report->sprint_id === null) {
                $report->sprint_id = $sprint->id;
                $report->save();
            }
            
            // Update report name if it contains "Unknown"
            if (strpos($report->report_name, 'Unknown') !== false) {
                $originalName = $report->report_name;
                $report->report_name = str_replace('Sprint Unknown', "Sprint {$sprint->sprint_number}", $report->report_name);
                
                if ($originalName !== $report->report_name) {
                    \Log::info("Updated report name from '{$originalName}' to '{$report->report_name}'");
                    $report->save();
                }
            }
            
            $reportsByTeam[$teamName][] = $report;
        }
        
        // Add version numbers to reports from the same team
        foreach ($reportsByTeam as $teamName => $reports) {
            \Log::info("Team: {$teamName}, Report Count: " . count($reports));
            
            // Sort reports by created_at (oldest first)
            usort($reports, function($a, $b) {
                return $a->created_at->timestamp - $b->created_at->timestamp;
            });
            
            // Assign version numbers (oldest = v1, second oldest = v2, etc.)
            $startVersion = 1;
            foreach ($reports as $report) {
                \Log::info("Setting version for report ID {$report->id}: v{$startVersion}");
                $reportVersions[$report->id] = "v{$startVersion}";
                $startVersion++;
            }
            
            // Update the sorted reports in the array
            $reportsByTeam[$teamName] = $reports;
        }
        
        \Log::info("Report Versions array: " . json_encode($reportVersions));
        
        // Format sprint dates using DateHelper
        $sprint->formatted_start_date = DateHelper::formatSprintDate($sprint->start_date);
        $sprint->formatted_end_date = DateHelper::formatSprintDate($sprint->end_date);
        
        return view('reports.sprints.show', compact('sprint', 'reportsByTeam', 'reportVersions'));
    }
    
    /**
     * Display a specific report's details.
     *
     * @param int $reportId
     * @return \Illuminate\Http\Response
     */
    public function showReport($reportId)
    {
        $report = SprintReport::with('sprint', 'user')->findOrFail($reportId);
        
        // If report doesn't have a sprint, try to find an appropriate one
        if (!$report->sprint) {
            // Look for a sprint with the number that might be in the report name
            $matches = [];
            if (preg_match('/Sprint\s+(\d+)/i', $report->report_name, $matches)) {
                $sprintNumber = (int)$matches[1];
                $sprint = Sprint::where('sprint_number', $sprintNumber)->first();
                
                if ($sprint) {
                    $report->sprint_id = $sprint->id;
                    $report->save();
                    // Reload the report with the newly associated sprint
                    $report = SprintReport::with('sprint', 'user')->findOrFail($reportId);
                } else {
                    // If no matching sprint found, create a fake sprint object for the view
                    $report->sprint = new Sprint([
                        'sprint_number' => $sprintNumber,
                        'start_date' => now()->subDays(7),
                        'end_date' => now(),
                        'status' => 'completed',
                        'progress_percentage' => 100
                    ]);
                }
            } else {
                // If no sprint number in name, use current sprint
                $sprint = Sprint::getCurrentSprint();
                if ($sprint) {
                    $report->sprint_id = $sprint->id;
                    $report->save();
                    // Reload the report with the newly associated sprint
                    $report = SprintReport::with('sprint', 'user')->findOrFail($reportId);
                } else {
                    // Last resort - create a fake sprint object for the view
                    $report->sprint = new Sprint([
                        'sprint_number' => 'N/A',
                        'start_date' => now()->subDays(7),
                        'end_date' => now(),
                        'status' => 'unknown',
                        'progress_percentage' => 0
                    ]);
                }
            }
        }
        
        // Find all reports for the same team in the same sprint
        $teamReports = SprintReport::where('board_name', $report->board_name)
            ->where('sprint_id', $report->sprint_id)
            ->orderBy('created_at', 'asc')
            ->get();
        
        // Determine the version of this report based on creation date (oldest first = v1)
        $reportVersion = 'v1'; // Default version
        $thisReportDate = $report->created_at;
        $versionNumber = 1;
        
        // Count how many older reports this team has in the same sprint
        foreach ($teamReports as $teamReport) {
            if ($teamReport->id == $report->id) {
                $reportVersion = "v{$versionNumber}";
                break;
            }
            $versionNumber++;
        }
        
        \Log::info("Report ID {$report->id} determined to be version {$reportVersion} for Sprint #{$report->sprint->sprint_number}");
        
        // Extract story points and bug data from the JSON
        $storyPointsData = $report->getStoryPointsStructuredAttribute();
        $bugCardsData = $report->getBugCardsStructuredAttribute();
        
        // Create view variables similar to the original report page
        $boardName = $report->board_name;
        $reportName = $report->report_name;
        $notes = $report->notes;
        $isSavedReport = true;
        
        // Extract team members data - ensure we capture all fields including 'extra'
        $teamMembers = $storyPointsData['teamMembers'] ?? [];
        
        // Make sure each team member has all required fields
        foreach ($teamMembers as &$member) {
            $member['pointPersonal'] = $member['pointPersonal'] ?? 0;
            $member['pass'] = $member['pass'] ?? 0;
            $member['bug'] = $member['bug'] ?? 0;
            $member['cancel'] = $member['cancel'] ?? 0;
            $member['extra'] = $member['extra'] ?? 0;
            $member['final'] = $member['final'] ?? 0;
            $member['passPercent'] = $member['passPercent'] ?? '0%';
        }
        
        // Extract summary data
        $summaryData = $storyPointsData['summary'] ?? [];
        
        // Ensure all summary fields are present
        $summaryData['planPoints'] = $summaryData['planPoints'] ?? 0;
        $summaryData['actualPoints'] = $summaryData['actualPoints'] ?? 0;
        $summaryData['remainPercent'] = $summaryData['remainPercent'] ?? '0%';
        $summaryData['percentComplete'] = $summaryData['percentComplete'] ?? '0%';
        $summaryData['currentSprintPoints'] = $summaryData['currentSprintPoints'] ?? 0;
        $summaryData['actualCurrentSprint'] = $summaryData['actualCurrentSprint'] ?? 0;
        $summaryData['boardName'] = $boardName;
        $summaryData['lastUpdated'] = $summaryData['lastUpdated'] ?? '';
        
        // Extract totals
        $totals = $storyPointsData['totals'] ?? [];
        
        // Ensure all totals fields are present
        $totals['totalPersonal'] = $totals['totalPersonal'] ?? 0;
        $totals['totalPass'] = $totals['totalPass'] ?? 0;
        $totals['totalBug'] = $totals['totalBug'] ?? 0;
        $totals['totalCancel'] = $totals['totalCancel'] ?? 0;
        $totals['totalExtra'] = $totals['totalExtra'] ?? 0;
        $totals['totalFinal'] = $totals['totalFinal'] ?? 0;
        
        // Extract bug cards
        $bugCards = $bugCardsData['bugCards'] ?? [];
        $bugCount = $bugCardsData['bugCount'] ?? '0 bugs';
        $totalBugPoints = $bugCardsData['totalBugPoints'] ?? 0;
        
        // Process bug cards to ensure they have consistent data
        foreach ($bugCards as &$bug) {
            $bug['name'] = $bug['name'] ?? 'Unknown Bug';
            $bug['points'] = $bug['points'] ?? 0;
            $bug['list'] = $bug['list'] ?? '';
            $bug['description'] = $bug['description'] ?? '';
            $bug['members'] = $bug['members'] ?? 'Not assigned';
            $bug['priorityClass'] = $bug['priorityClass'] ?? 'priority-none';
        }
        
        // Create sprint info for display
        $sprintInfo = [
            'number' => $report->sprint->sprint_number ?? 'Unknown',
            'startDate' => $report->sprint->formatted_start_date ?? 'N/A',
            'endDate' => $report->sprint->formatted_end_date ?? 'N/A',
            'progress' => $report->sprint->progress_percentage ?? 0,
        ];
        
        // Get backlog bugs from either the current report's backlog_data, or fetch from BacklogController
        // Initialize backlog variables
        $backlogCards = [];
        $backlogBugCount = '0 bugs';
        $backlogTotalPoints = 0;
        
        // First try to get backlog data from the current report if available
        $backlogData = null;
        if (isset($report->backlog_data) && !empty($report->backlog_data)) {
            // Check if backlog_data is already an array or needs to be decoded
            if (is_array($report->backlog_data)) {
                $backlogData = $report->backlog_data;
            } else {
                $backlogData = json_decode($report->backlog_data, true);
            }
            
            if ($backlogData) {
                $backlogCards = $backlogData['bugCards'] ?? [];
                $backlogBugCount = $backlogData['bugCount'] ?? '0 bugs';
                $backlogTotalPoints = $backlogData['totalBugPoints'] ?? 0;
            }
        }
        
        // Return the view with all necessary data
        return view('reports.sprints.report', compact(
            'report',
            'reportName',
            'boardName',
            'notes',
            'teamMembers',
            'summaryData',
            'totals',
            'bugCards',
            'bugCount',
            'totalBugPoints',
            'backlogCards',
            'backlogBugCount',
            'backlogTotalPoints',
            'reportVersion',
            'sprintInfo'
        ));
    }
    
    /**
     * Get the priority of a bug.
     *
     * @param array $bug
     * @return string
     */
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

    /**
     * Get numeric value for priority for sorting.
     *
     * @param string $priority
     * @return int
     */
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
     * Delete a specific sprint report.
     *
     * @param int $reportId
     * @return \Illuminate\View\View
     */
    public function delete($reportId)
    {
        try {
            // Check if user is an admin can delete reports
            // If not, redirect back with an error message
            if (!auth()->user()->isAdmin()) {
                return redirect()->back()->with('error', 'Access denied. Only administrators can delete reports.');
            }
            $report = SprintReport::findOrFail($reportId);
            $sprintId = $report->sprint_id;
            $report->delete();
            
            // Use direct routing to show sprint
            $sprint = \App\Models\Sprint::findOrFail($sprintId);
            $reports = SprintReport::where('sprint_id', $sprintId)
                ->orderBy('created_at', 'desc')
                ->get();
                
            return view('reports.sprints.show', [
                'sprint' => $sprint,
                'reports' => $reports,
                'success' => 'Report deleted successfully.'
            ]);
        } catch (\Exception $e) {
            // Use direct routing to index
            $sprints = \App\Models\Sprint::orderBy('sprint_number', 'desc')->get();
            return view('reports.sprints.index', [
                'sprints' => $sprints,
                'error' => 'Error deleting report: ' . $e->getMessage()
            ]);
        }
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
                // Get current user's full name or name
                $user = auth()->user();
                $userName = $user->full_name ?? $user->name;
                
                // Get all boards with members
                $options = [
                    'members' => true,
                    'member_fields' => 'fullName'
                ];
                
                $boards = $this->trelloService->getBoards(['id', 'name'], $options);
                
                // Filter for boards where user is a direct member
                foreach ($boards as $board) {
                    if (isset($board['members']) && is_array($board['members'])) {
                        foreach ($board['members'] as $member) {
                            if (isset($member['fullName']) && $member['fullName'] === $userName) {
                                $userTeams[] = $board['name'];
                                break;
                            }
                        }
                    }
                }
                
                // Allow users to see data from all teams they belong to
                // Removed the limitation to only see the first team
            } catch (\Exception $e) {
                // Log error but don't fail - empty team list will result
                \Log::error('Error fetching user teams: ' . $e->getMessage());
            }
        }
        
        return $userTeams;
    }

    /**
     * Get all reports for the teams the current user belongs to
     *
     * @return \Illuminate\Http\Response
     */
    public function getUserReports()
    {
        // Get user teams and ensure no duplicates
        $userTeams =$this->getUserTeams();
        
        // Get all reports
        $allReports = SprintReport::with(['sprint', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Filter reports by user teams
        $teamReports = [];
        
        foreach ($allReports as $report) {
            if (in_array($report->board_name, $userTeams)) {
                if (!isset($teamReports[$report->board_name])) {
                    $teamReports[$report->board_name] = [];
                }
                $teamReports[$report->board_name][] = $report;
            }
        }
        
        // Group reports by sprint
        $reportsBySprint = [];
        
        foreach ($teamReports as $teamName => $reports) {
            foreach ($reports as $report) {
                $sprintId = $report->sprint->id;
                $sprintNumber = $report->sprint->sprint_number;
                
                if (!isset($reportsBySprint[$sprintId])) {
                    $reportsBySprint[$sprintId] = [
                        'sprint' => $report->sprint,
                        'teams' => []
                    ];
                }
                
                if (!isset($reportsBySprint[$sprintId]['teams'][$teamName])) {
                    $reportsBySprint[$sprintId]['teams'][$teamName] = [];
                }
                
                $reportsBySprint[$sprintId]['teams'][$teamName][] = $report;
            }
        }
        
        // Sort sprints by number (descending)
        uasort($reportsBySprint, function($a, $b) {
            return $b['sprint']->sprint_number <=> $a['sprint']->sprint_number;
        });
        
        return view('reports.user-reports', [
            'reportsBySprint' => $reportsBySprint,
            'userTeams' => $userTeams
        ]);
    }
} 