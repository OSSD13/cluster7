<?php
/*
 * BacklogController
 * This controller manages the backlog tasks and their associated operations.
 * @author : นายนพรัตน อุดมเลิศ 66160359/ นางสาวนิญาดา บุตรจันทร์ 66160361/นายปวริศ สินชุม 66160233
 * @Create Date : 2025-04-11
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SprintReport;
use App\Models\Sprint;
use App\Helpers\DateHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
        if (!Auth::user()->isAdmin()) {
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
        if (!Auth::user()->isAdmin()) {
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
                $userName = Auth::user()->name;

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
                Log::error('Error fetching user teams: ' . $e->getMessage());
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
            // Log the incoming request for debugging
            Log::info('Attempting to delete bug', ['id' => $id]);

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
                    Log::info('Saving modified report', ['report_id' => $report->id]);
                    $report->report_data = $reportData;
                    $report->save();
                }
            }
            // If the bug was found and removed, return success response
            if ($bugFound) {
                return redirect()->route('backlog.index')->with('success', 'Bug deleted successfully');
            } else {
                return redirect()->route('backlog.index')->with('error', 'Bug not found');
            }
        } catch (\Exception $e) {
            return redirect()->route('backlog.index')->with('error', 'Error deleting bug: ' . $e->getMessage());
        }
    }

    /**
     * Update a bug in the backlog.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try {
            // Log the incoming request for debugging
            Log::info('Attempting to update bug', ['id' => $id, 'data' => $request->all()]);

            // Validate request data
            $validated = $request->validate([
                'name' => 'required|string',
                'points' => 'required|integer|min:0',
                'description' => 'nullable|string',
                'team' => 'required|string'
            ]);

            // Get all reports
            $reports = SavedReport::with('sprint')->get();
            $bugFound = false;

            // Iterate through each report to find and update the bug
            foreach ($reports as $report) {
                $reportData = $report->report_data;
                if (is_string($reportData)) {
                    $reportData = json_decode($reportData, true);
                }
                $modified = false;

                // Check in bug_cards section
                if (isset($reportData['bug_cards']) && is_array($reportData['bug_cards'])) {
                    foreach ($reportData['bug_cards'] as $teamName => &$bugs) {
                        foreach ($bugs as $key => &$bug) {
                            if ((isset($bug['id']) && $bug['id'] === $id) ||
                                (isset($bug['id']) && is_numeric($id) && strpos($bug['id'], $id) !== false)) {
                                Log::info('Found bug to update in bug_cards', [
                                    'team' => $teamName,
                                    'bug_id' => $bug['id'],
                                    'bug_name' => $bug['name'] ?? 'N/A'
                                ]);
                                $bug['name'] = $validated['name'];
                                $bug['team'] = $validated['team'];
                                $bug['points'] = $validated['points'];
                                $bug['description'] = $validated['description'];
                                $modified = true;
                                $bugFound = true;
                            }
                        }
                    }
                }

                // Check in backlog section
                if (isset($reportData['backlog']) && is_array($reportData['backlog'])) {
                    foreach ($reportData['backlog'] as $teamName => &$bugs) {
                        foreach ($bugs as $key => &$bug) {
                            if ((isset($bug['id']) && $bug['id'] === $id) ||
                                (isset($bug['id']) && is_numeric($id) && strpos($bug['id'], $id) !== false)) {
                                Log::info('Found bug to update in backlog', [
                                    'team' => $teamName,
                                    'bug_id' => $bug['id'],
                                    'bug_name' => $bug['name'] ?? 'N/A'
                                ]);
                                $bug['name'] = $validated['name'];
                                $bug['team'] = $validated['team'];
                                $bug['points'] = $validated['points'];
                                $bug['description'] = $validated['description'];
                                $modified = true;
                                $bugFound = true;
                            }
                        }
                    }
                }

                // If the report was modified, save it
                if ($modified) {
                    Log::info('Saving modified report', ['report_id' => $report->id]);
                    $report->report_data = $reportData;
                    $report->save();
                }
            }

            if ($bugFound) {
                Log::info('Bug updated successfully');
                // Check if request wants JSON response
                if (request()->wantsJson()) {
                    return response()->json(['message' => 'Bug updated successfully']);
                }
                // Otherwise redirect back with success message
                return redirect()->back()->with('success', 'Bug updated successfully');
            } else {
                Log::warning('Bug not found for update', ['id' => $id]);
                if (request()->wantsJson()) {
                    return response()->json(['error' => 'Bug not found'], 404);
                }
                return redirect()->back()->with('error', 'Bug not found');
            }
        } catch (\Exception $e) {
            Log::error('Error updating bug', [
                'id' => $id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            if (request()->wantsJson()) {
                return response()->json(['error' => 'Error updating bug: ' . $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', 'Error updating bug: ' . $e->getMessage());
        }
    }

    /**
     * Update the status of a backlog bug.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $id
     * @return \Illuminate\Http\Response
     */
    public function updateStatus(Request $request, $id)
    {
        $status = $request->input('status');
        $points = $request->input('points', 0);

        // Validate input
        if (!in_array($status, ['active', 'completed'])) {
            return response()->json(['error' => 'Invalid status. Must be "active" or "completed".'], 422);
        }

        // Get all saved reports
        $reports = SavedReport::with('sprint')->get();
        $updated = false;
        $updatedReport = null;

        DB::beginTransaction();

        try {
            // Update the status in each report containing this bug
            foreach ($reports as $report) {
                // Handle cases where report_data is already decoded or is a string
                $reportData = $report->report_data;
                if (is_string($reportData)) {
                    $reportData = json_decode($reportData, true);
                }

                // Check backlog data
                $reportUpdated = false;

                if (isset($reportData['backlog']) && is_array($reportData['backlog'])) {
                    foreach ($reportData['backlog'] as $teamName => &$bugs) {
                        foreach ($bugs as &$bug) {
                            if (isset($bug['id']) && $bug['id'] == $id) {
                                // Update status
                                $oldStatus = $bug['status'] ?? 'active';
                                $bug['status'] = $status;

                                // If status is changing from active to completed
                                // Update the actual points in the story_points_data
                                if ($oldStatus !== 'completed' && $status === 'completed' && $points > 0) {
                                    // Get story_points_data
                                    if (isset($reportData['story_points_data'])) {
                                        $storyPointsData = $reportData['story_points_data'];

                                        // If it's a JSON string, decode it
                                        if (is_string($storyPointsData)) {
                                            $storyPointsData = json_decode($storyPointsData, true);
                                        }

                                        // Update actual points if there's a summary section
                                        if (is_array($storyPointsData) && isset($storyPointsData['summary'])) {
                                            // Add the points to actual points
                                            $actualPoints = floatval($storyPointsData['summary']['actualPoints'] ?? 0);
                                            $storyPointsData['summary']['actualPoints'] = $actualPoints + floatval($points);

                                            // Recalculate percentages
                                            $planPoints = floatval($storyPointsData['summary']['planPoints'] ?? 0);
                                            if ($planPoints > 0) {
                                                $newActualPoints = $actualPoints + floatval($points);
                                                $percentComplete = round(($newActualPoints / $planPoints) * 100, 2);
                                                $remainPercent = 100 - $percentComplete;

                                                $storyPointsData['summary']['percentComplete'] = $percentComplete . '%';
                                                $storyPointsData['summary']['remainPercent'] = $remainPercent . '%';
                                            }

                                            // Update the story_points_data in the report
                                            $reportData['story_points_data'] = $storyPointsData;
                                        }
                                    }
                                }

                                $reportUpdated = true;
                                $updated = true;
                            }
                        }
                    }
                }

                // Check bug cards data
                if (!$reportUpdated && isset($reportData['bug_cards']) && is_array($reportData['bug_cards'])) {
                    foreach ($reportData['bug_cards'] as $teamName => &$bugs) {
                        foreach ($bugs as &$bug) {
                            if (isset($bug['id']) && $bug['id'] == $id) {
                                // Update status
                                $oldStatus = $bug['status'] ?? 'active';
                                $bug['status'] = $status;

                                // If status is changing from active to completed
                                // Update the actual points in the story_points_data
                                if ($oldStatus !== 'completed' && $status === 'completed' && $points > 0) {
                                    // Get story_points_data
                                    if (isset($reportData['story_points_data'])) {
                                        $storyPointsData = $reportData['story_points_data'];

                                        // If it's a JSON string, decode it
                                        if (is_string($storyPointsData)) {
                                            $storyPointsData = json_decode($storyPointsData, true);
                                        }

                                        // Update actual points if there's a summary section
                                        if (is_array($storyPointsData) && isset($storyPointsData['summary'])) {
                                            // Add the points to actual points
                                            $actualPoints = floatval($storyPointsData['summary']['actualPoints'] ?? 0);
                                            $storyPointsData['summary']['actualPoints'] = $actualPoints + floatval($points);

                                            // Recalculate percentages
                                            $planPoints = floatval($storyPointsData['summary']['planPoints'] ?? 0);
                                            if ($planPoints > 0) {
                                                $newActualPoints = $actualPoints + floatval($points);
                                                $percentComplete = round(($newActualPoints / $planPoints) * 100, 2);
                                                $remainPercent = 100 - $percentComplete;

                                                $storyPointsData['summary']['percentComplete'] = $percentComplete . '%';
                                                $storyPointsData['summary']['remainPercent'] = $remainPercent . '%';
                                            }

                                            // Update the story_points_data in the report
                                            $reportData['story_points_data'] = $storyPointsData;
                                        }
                                    }
                                }

                                $updated = true;
                                $updatedReport = $report;
                            }
                        }
                    }
                }

                // Save the updated report data
                if ($reportUpdated) {
                    $report->report_data = $reportData;
                    $report->save();
                }
            }

            DB::commit();

            if ($updated) {
                return response()->json([
                    'success' => true,
                    'message' => 'Bug status updated successfully',
                    'status' => $status
                ]);
            } else {
                return response()->json(['error' => 'Bug not found'], 404);
            }
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating backlog bug status: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update bug. ' . $e->getMessage()], 500);
        }
    }
}
