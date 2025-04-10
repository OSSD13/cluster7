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
            // For non-admin users, get their teams first
            $userTeams = $this->getUserTeams();
            \Log::info('SprintReportController: User teams for filtering', ['user' => auth()->user()->name, 'teams' => $userTeams]);

            // Get all sprints initially, then filter reports
            $sprints = Sprint::with(['reports' => function($query) {
                // Order reports within the query
                $query->orderBy('created_at', 'desc');
            }])
                ->orderBy('sprint_number', 'desc')
                ->get();

            // Now, filter the reports collection for each sprint
            foreach ($sprints as $sprint) {
                $initialReportCount = $sprint->reports->count();
                if (!empty($userTeams)) {
                    // Filter the loaded reports collection
                    $sprint->reports = $sprint->reports->filter(function($report) use ($userTeams) {
                        $boardName = $report->board_name ?? 'null'; // Handle null board names
                        $isMember = isset($report->board_name) && in_array($report->board_name, $userTeams);
                        // Log each comparison
                        \Log::debug('SprintReportController: Filtering report', [
                            'report_id' => $report->id,
                            'report_board_name' => $boardName,
                            'user_teams' => $userTeams,
                            'is_member' => $isMember
                        ]);
                        return $isMember;
                    });
                } else {
                    // If user has no teams, they should see no reports
                    \Log::debug('SprintReportController: User has no teams, clearing reports for sprint', ['sprint_id' => $sprint->id]);
                    $sprint->reports = collect([]);
                }
                $filteredReportCount = $sprint->reports->count();
                if ($initialReportCount !== $filteredReportCount) {
                    \Log::info('SprintReportController: Filtered reports for sprint', [
                        'sprint_id' => $sprint->id,
                        'sprint_number' => $sprint->sprint_number,
                        'initial_count' => $initialReportCount,
                        'filtered_count' => $filteredReportCount,
                        'user_teams' => $userTeams
                    ]);
                }
            }

            // Remove sprints that have no reports *after* filtering
            $sprints = $sprints->filter(function($sprint) {
                return $sprint->reports->isNotEmpty();
            });

            \Log::info('SprintReportController: Final sprint count for user', [
                'user' => auth()->user()->name,
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

        // Group reports by team and calculate versions
        $reportsByTeam = [];
        $reportVersions = [];
        
        // First, group reports by team
        foreach ($sprint->reports as $report) {
            $teamName = $report->board_name;
            if (!isset($reportsByTeam[$teamName])) {
                $reportsByTeam[$teamName] = [];
            }
            $reportsByTeam[$teamName][] = $report;
        }

        // Then calculate versions for each team's reports
        foreach ($reportsByTeam as $teamName => $reports) {
            // Sort reports by creation date (oldest first)
            usort($reports, function($a, $b) {
                return strtotime($a->created_at) - strtotime($b->created_at);
            });

            // Assign version numbers (oldest = v1, second oldest = v2, etc.)
            foreach ($reports as $index => $report) {
                $reportVersions[$report->id] = 'v' . ($index + 1);
            }

            // Update the sorted reports in the array
            $reportsByTeam[$teamName] = $reports;
        }

        // If user is not admin, filter reports to show only their teams
        if (!auth()->user()->isAdmin()) {
            $userTeams = $this->getUserTeams();
            $reportsByTeam = array_filter($reportsByTeam, function($teamName) use ($userTeams) {
                return in_array($teamName, $userTeams);
            }, ARRAY_FILTER_USE_KEY);
        }

        return view('reports.sprints.show', compact('sprint', 'reportsByTeam', 'reportVersions'));
    }
    
    /**
     * Display a specific report's details.
     *
     * @param int $reportId
     * @return \Illuminate\Http\Response
     */
    public function show($reportId)
    {
        $report = SprintReport::with('sprint')->findOrFail($reportId);
        $sprint = $report->sprint;
        
        // Extract story points and bug data from the JSON
        $storyPointsData = $report->getStoryPointsStructuredAttribute();
        $bugCardsData = $report->getBugCardsStructuredAttribute();
        
        // Create view variables
        $boardName = $report->board_name;
        $reportName = $report->report_name;
        $notes = $report->notes;
        
        // Extract team members data
        $teamMembers = $storyPointsData['teamMembers'] ?? [];
        
        // Create summary data
        $summaryData = $storyPointsData['summary'] ?? [];
        $totals = $storyPointsData['totals'] ?? [];
        
        // Create bug cards data
        $bugCards = $bugCardsData['bugCards'] ?? [];
        $bugCount = $bugCardsData['bugCount'] ?? '0 bugs';
        $totalBugPoints = $bugCardsData['totalBugPoints'] ?? 0;
        
        // Get backlog bugs from either the current report's backlog_data
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
        
        // Create sprint info
        $sprintInfo = [
            'number' => $sprint->sprint_number ?? 'Unknown',
            'startDate' => $sprint->formatted_start_date ?? 'N/A',
            'endDate' => $sprint->formatted_end_date ?? 'N/A',
            'progress' => $sprint->progress_percentage ?? 0,
        ];

        // Get all reports for this sprint
        $sprintReports = SprintReport::where('sprint_id', $sprint->id)
            ->orderBy('created_at', 'desc')
            ->get();

        // Group reports by team
        $reportsByTeam = [];
        foreach ($sprintReports as $sprintReport) {
            $teamName = $sprintReport->board_name;
            if (!isset($reportsByTeam[$teamName])) {
                $reportsByTeam[$teamName] = [];
            }
            $reportsByTeam[$teamName][] = $sprintReport;
        }
        
        return view('reports.sprints.show', compact(
            'report',
            'sprint',
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
            'sprintInfo',
            'reportsByTeam'
        ));
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
            if (!auth()->user()->isAdmin()) {
                return redirect()->back()->with('error', 'Access denied. Only administrators can delete reports.');
            }
            
            $report = SprintReport::findOrFail($reportId);
            $sprintId = $report->sprint_id;
            $report->delete();
            
            // Get the sprint with its reports
            $sprint = Sprint::with(['reports' => function($query) {
                $query->orderBy('created_at', 'desc');
            }])->findOrFail($sprintId);

            // Group reports by team and calculate versions
            $reportsByTeam = [];
            $reportVersions = [];
            
            // First, group reports by team
            foreach ($sprint->reports as $report) {
                $teamName = $report->board_name;
                if (!isset($reportsByTeam[$teamName])) {
                    $reportsByTeam[$teamName] = [];
                }
                $reportsByTeam[$teamName][] = $report;
            }

            // Then calculate versions for each team's reports
            foreach ($reportsByTeam as $teamName => $reports) {
                // Sort reports by creation date (oldest first)
                usort($reports, function($a, $b) {
                    return strtotime($a->created_at) - strtotime($b->created_at);
                });

                // Assign version numbers (oldest = v1, second oldest = v2, etc.)
                foreach ($reports as $index => $report) {
                    $reportVersions[$report->id] = 'v' . ($index + 1);
                }

                // Update the sorted reports in the array
                $reportsByTeam[$teamName] = $reports;
            }

            // If user is not admin, filter reports to show only their teams
            if (!auth()->user()->isAdmin()) {
                $userTeams = $this->getUserTeams();
                $reportsByTeam = array_filter($reportsByTeam, function($teamName) use ($userTeams) {
                    return in_array($teamName, $userTeams);
                }, ARRAY_FILTER_USE_KEY);
            }

            return view('reports.sprints.show', compact('sprint', 'reportsByTeam', 'reportVersions'))
                ->with('success', 'Report deleted successfully.');
                
        } catch (\Exception $e) {
            return redirect()->route('sprints.index')
                ->with('error', 'Error deleting report: ' . $e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified sprint report.
     *
     * @param int $reportId
     * @return \Illuminate\Http\Response
     */
    public function edit($reportId)
    {
        $report = SprintReport::findOrFail($reportId);
        
        // Allow admin and tester to edit
        if (!auth()->user()->isAdmin() && auth()->user()->role !== 'tester') {
            return redirect()->back()->with('error', 'Access denied');
        }

        // Check if the sprint has ended
        $sprint = $report->sprint;
        $today = now()->startOfDay();
        $sprintEndDate = $sprint->end_date->startOfDay();
        
        if ($today->lessThan($sprintEndDate)) {
            return redirect()->back()->with('error', 'Reports can only be edited after the sprint has ended.');
        }

        // Check if this is the latest version of the report
        $latestReport = SprintReport::where('sprint_id', $sprint->id)
            ->where('board_name', $report->board_name)
            ->orderBy('created_at', 'desc')
            ->first();

        if ($report->id !== $latestReport->id) {
            return redirect()->back()->with('error', 'You can only edit the latest version of a report. Please edit version ' . $latestReport->report_name);
        }

        // Extract story points and bug data from the JSON
        $storyPointsData = $report->getStoryPointsStructuredAttribute();
        $bugCardsData = $report->getBugCardsStructuredAttribute();
        
        // Create view variables
        $boardName = $report->board_name;
        $reportName = $report->report_name;
        $notes = $report->notes;
        
        // Extract team members data - ensure we capture all fields including 'extra'
        $teamMembers = $storyPointsData['teamMembers'] ?? [];
        
        // Make sure each team member has all required fields with default values
        foreach ($teamMembers as &$member) {
            $member['name'] = $member['name'] ?? 'Unknown Member';
            $member['pointPersonal'] = isset($member['pointPersonal']) ? (int)$member['pointPersonal'] : 0;
            $member['pass'] = isset($member['pass']) ? (int)$member['pass'] : 0;
            $member['bug'] = isset($member['bug']) ? (int)$member['bug'] : 0;
            $member['cancel'] = isset($member['cancel']) ? (int)$member['cancel'] : 0;
            $member['extra'] = isset($member['extra']) ? (int)$member['extra'] : 0;
            $member['final'] = isset($member['final']) ? (int)$member['final'] : 0;
            $member['passPercent'] = isset($member['passPercent']) ? $member['passPercent'] : '0%';
            $member['completed'] = isset($member['completed']) ? (int)$member['completed'] : 0;
            $member['inProgress'] = isset($member['inProgress']) ? (int)$member['inProgress'] : 0;
            $member['notStarted'] = isset($member['notStarted']) ? (int)$member['notStarted'] : 0;
            $member['total'] = isset($member['total']) ? (int)$member['total'] : 0;
        }
        
        // Create summary data with default 0 for points
        $summaryData = $storyPointsData['summary'] ?? [];
        
        // Ensure all summary fields are present with default 0 for points
        $summaryData['planPoints'] = isset($summaryData['planPoints']) ? (int)$summaryData['planPoints'] : 0;
        $summaryData['actualPoints'] = isset($summaryData['actualPoints']) ? (int)$summaryData['actualPoints'] : 0;
        $summaryData['remainPercent'] = isset($summaryData['remainPercent']) ? $summaryData['remainPercent'] : '0%';
        $summaryData['percentComplete'] = isset($summaryData['percentComplete']) ? $summaryData['percentComplete'] : '0%';
        $summaryData['currentSprintPoints'] = isset($summaryData['currentSprintPoints']) ? (int)$summaryData['currentSprintPoints'] : 0;
        $summaryData['actualCurrentSprint'] = isset($summaryData['actualCurrentSprint']) ? (int)$summaryData['actualCurrentSprint'] : 0;
        $summaryData['boardName'] = $boardName;
        $summaryData['lastUpdated'] = $summaryData['lastUpdated'] ?? '';
        
        // Extract totals with default 0 for points
        $totals = $storyPointsData['totals'] ?? [];
        
        // Ensure all totals fields are present with default 0 for points
        $totals['totalPersonal'] = isset($totals['totalPersonal']) ? (int)$totals['totalPersonal'] : 0;
        $totals['totalPass'] = isset($totals['totalPass']) ? (int)$totals['totalPass'] : 0;
        $totals['totalBug'] = isset($totals['totalBug']) ? (int)$totals['totalBug'] : 0;
        $totals['totalCancel'] = isset($totals['totalCancel']) ? (int)$totals['totalCancel'] : 0;
        $totals['totalExtra'] = isset($totals['totalExtra']) ? (int)$totals['totalExtra'] : 0;
        $totals['totalFinal'] = isset($totals['totalFinal']) ? (int)$totals['totalFinal'] : 0;
        $totals['completed'] = isset($totals['completed']) ? (int)$totals['completed'] : 0;
        $totals['inProgress'] = isset($totals['inProgress']) ? (int)$totals['inProgress'] : 0;
        $totals['notStarted'] = isset($totals['notStarted']) ? (int)$totals['notStarted'] : 0;
        $totals['total'] = isset($totals['total']) ? (int)$totals['total'] : 0;
        
        // Create bug cards data with default 0 for points
        $bugCards = $bugCardsData['bugCards'] ?? [];
        $bugCount = $bugCardsData['bugCount'] ?? '0 bugs';
        $totalBugPoints = isset($bugCardsData['totalBugPoints']) ? (int)$bugCardsData['totalBugPoints'] : 0;
        
        // Ensure all bug cards have required fields with default 0 for points
        foreach ($bugCards as &$bug) {
            $bug['name'] = $bug['name'] ?? 'Unknown Bug';
            $bug['points'] = isset($bug['points']) ? (int)$bug['points'] : 0;
            $bug['list'] = $bug['list'] ?? 'Not Assigned';
            $bug['description'] = $bug['description'] ?? '';
            $bug['members'] = $bug['members'] ?? 'Not Assigned';
            $bug['priorityClass'] = $bug['priorityClass'] ?? 'priority-none';
        }
        
        // Get backlog bugs from either the current report's backlog_data
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
                $backlogTotalPoints = isset($backlogData['totalBugPoints']) ? (int)$backlogData['totalBugPoints'] : 0;
            }
        }
        
        // Create sprint info
        $sprintInfo = [
            'number' => $report->sprint->sprint_number ?? 'Unknown',
            'startDate' => $report->sprint->formatted_start_date ?? 'N/A',
            'endDate' => $report->sprint->formatted_end_date ?? 'N/A',
            'progress' => $report->sprint->progress_percentage ?? 0,
        ];
        
        return view('reports.sprints.edit', compact(
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
            'sprintInfo'
        ));
    }

    /**
     * Update the specified sprint report in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $reportId
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $reportId)
    {
        $originalReport = SprintReport::findOrFail($reportId);
        
        // Allow admin and tester to update
        if (!auth()->user()->isAdmin() && auth()->user()->role !== 'tester') {
            return redirect()->back()->with('error', 'Access denied');
        }

        // Check if the sprint has ended
        $sprint = $originalReport->sprint;
        $today = now()->startOfDay();
        $sprintEndDate = $sprint->end_date->startOfDay();
        
        if ($today->lessThan($sprintEndDate)) {
            return redirect()->back()->with('error', 'Reports can only be edited after the sprint has ended.');
        }
        
        // Validate the request data
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'story_points_data' => 'required|array',
            'bug_cards_data' => 'required|array',
        ]);

        try {
            // Get the current user's name
            $user = auth()->user();
            $editorName = $user->full_name ?? $user->name;
            
            // Get the next version number for this team's reports
            $currentTeamReports = SprintReport::where('sprint_id', $sprint->id)
                ->where('board_name', $originalReport->board_name)
                ->orderBy('created_at', 'asc')
                ->get();
            
            $nextVersion = $currentTeamReports->count() + 1;
            
            // Create a new report with the updated data
            $newReport = new SprintReport([
                'sprint_id' => $originalReport->sprint_id,
                'board_name' => $originalReport->board_name,
                'report_name' => $originalReport->report_name . " (v{$nextVersion})",
                'notes' => $validated['notes'],
                'story_points_data' => $validated['story_points_data'],
                'bug_cards_data' => $validated['bug_cards_data'],
                'backlog_data' => $originalReport->backlog_data,
                'user_id' => auth()->id(),
                'edited_by' => $editorName,
                'edited_at' => now(),
            ]);

            // Save the new report
            $newReport->save();

            return redirect()->route('sprint-reports.show', $newReport->id)
                ->with('success', "Version {$nextVersion} of the report has been created successfully.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error creating new version of the report: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Get the teams the current user belongs to
     *
     * @return array Array of team names the user is a member of
     */
    protected function getUserTeams()
    {
        $user = auth()->user();
        
        // If user is admin, they can see all teams
        if ($user->isAdmin()) {
            return SprintReport::distinct('board_name')->pluck('board_name')->toArray();
        }
        
        // If user is tester, they can see all teams
        if ($user->role === 'tester') {
            return SprintReport::distinct('board_name')->pluck('board_name')->toArray();
        }

        // Get user's name
        $userName = $user->full_name ?? $user->name;
        
        // Get all reports where this user is mentioned in team members
        $reports = SprintReport::where(function($query) use ($userName) {
            $query->whereJsonContains('story_points_data->teamMembers', ['name' => $userName])
                  ->orWhereJsonContains('story_points_data->teamMembers', ['name' => strtolower($userName)])
                  ->orWhereJsonContains('story_points_data->teamMembers', ['name' => strtoupper($userName)]);
        })->get();
        
        // Extract unique board names from these reports
        $userTeams = $reports->pluck('board_name')->unique()->values()->toArray();
        
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