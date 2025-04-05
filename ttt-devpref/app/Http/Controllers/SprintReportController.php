<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sprint;
use App\Models\SprintReport;
use App\Helpers\DateHelper;
use Illuminate\Support\Facades\Auth;

class SprintReportController extends Controller
{
    /**
     * Display a listing of saved sprint reports.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get all sprints with their reports, ordered by latest sprint first
        $sprints = Sprint::with(['reports' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])
            ->orderBy('sprint_number', 'desc')
            ->get();
            
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
        
        // Extract team members data
        $teamMembers = $storyPointsData['teamMembers'] ?? [];
        
        // Extract summary data
        $summaryData = $storyPointsData['summary'] ?? [];
        
        // Override boardName from summary to ensure consistency
        if (isset($summaryData['boardName'])) {
            $summaryData['boardName'] = $boardName;
        }
        
        // Extract totals
        $totals = $storyPointsData['totals'] ?? [];
        
        // Extract bug cards
        $bugCards = $bugCardsData['bugCards'] ?? [];
        $bugCount = $bugCardsData['bugCount'] ?? '0 bugs';
        $totalBugPoints = $bugCardsData['totalBugPoints'] ?? 0;
        
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
        
        // If no backlog data in current report, get backlog data from all reports
        if (empty($backlogCards)) {
            // Get all saved reports with their sprints
            $savedReports = \App\Models\SavedReport::with('sprint')->get();
            $allBacklogBugs = collect();
            
            // Loop through all reports to collect backlog bugs
            foreach ($savedReports as $savedReport) {
                // Handle cases where report_data is already decoded or is a string
                $reportData = $savedReport->report_data;
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
                                $bug['sprint_number'] = $savedReport->sprint->sprint_number;
                                // Avoid duplicates by using ID as key
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
                                $bug['sprint_number'] = $savedReport->sprint->sprint_number;
                                // Avoid duplicates by using ID as key
                                $allBacklogBugs->put($bug['id'], $bug);
                            }
                        }
                    }
                }
            }
            
            // Filter to only show bugs for this team if we have team information
            if (!empty($boardName)) {
                $allBacklogBugs = $allBacklogBugs->filter(function ($bug) use ($boardName) {
                    return isset($bug['team']) && $bug['team'] === $boardName;
                });
            }
            
            // Sort bugs by priority
            $allBacklogBugs = $allBacklogBugs->sortBy(function ($bug) {
                return $this->getPriorityValue($this->getBugPriority($bug));
            });
            
            // Update backlog variables
            $backlogCards = $allBacklogBugs->values()->all();
            $backlogBugCount = count($backlogCards) . ' ' . \Illuminate\Support\Str::plural('bug', count($backlogCards));
            $backlogTotalPoints = collect($backlogCards)->sum('points');
        }
        
        // Additional sprint information using DateHelper for consistent formatting
        $sprintInfo = [
            'number' => $report->sprint->sprint_number,
            'startDate' => DateHelper::formatSprintDate($report->sprint->start_date),
            'endDate' => DateHelper::formatSprintDate($report->sprint->end_date),
            'progress' => $report->sprint->progress_percentage,
        ];
        
        // Format report creation date in 24-hour format
        $report->formatted_created_at = DateHelper::formatDateTime($report->created_at);
        
        // Return view with all the extracted data
        return view('reports.sprints.report', compact(
            'report',
            'reportVersion',
            'storyPointsData',
            'bugCardsData',
            'boardName',
            'reportName',
            'notes',
            'isSavedReport',
            'teamMembers',
            'summaryData',
            'totals',
            'bugCards',
            'bugCount',
            'totalBugPoints',
            'sprintInfo',
            'backlogCards',
            'backlogBugCount',
            'backlogTotalPoints'
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
     * @return \Illuminate\Http\Response
     */
    public function delete($reportId)
    {
        try {
            $report = SprintReport::findOrFail($reportId);
            
            // Check if user has permission to delete this report
            // For now, we'll allow any authenticated user to delete reports
            // In a production environment, you might want to add role-based permissions
            
            $sprintId = $report->sprint_id;
            $report->delete();
            
            return redirect()->route('sprints.show', $sprintId)
                ->with('success', 'Report deleted successfully.');
        } catch (\Exception $e) {
            return redirect()->route('sprints.index')
                ->with('error', 'Error deleting report: ' . $e->getMessage());
        }
    }
} 