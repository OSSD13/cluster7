<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SprintReport;
use App\Models\Sprint;
use App\Helpers\DateHelper;
use Illuminate\Support\Facades\DB;
use App\Models\SavedReport;
use Illuminate\Pagination\LengthAwarePaginator;

class BacklogController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
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

        // Paginate the sorted bugs manually (10 items per page)
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;
        $paginatedBugs = new LengthAwarePaginator(
            $sortedBacklogBugs->forPage($currentPage, $perPage),
            $sortedBacklogBugs->count(),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return view('backlog.index', [
            'allBugs' => $paginatedBugs, // Use the paginated data here
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
}
