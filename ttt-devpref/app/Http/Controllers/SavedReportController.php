<?php

namespace App\Http\Controllers;

use App\Models\SavedReport;
use App\Models\Sprint;
use App\Models\SprintReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SavedReportController extends Controller
{
    /**
     * Display a listing of saved reports.
     */
    public function index(Request $request)
    {
        $query = SavedReport::where('user_id', Auth::id());
        
        // Filter for sprint reports if specified
        if ($request->has('filter') && $request->filter === 'sprint') {
            // Get current sprint number from SprintSettingsController
            $sprintSettingsController = new \App\Http\Controllers\SprintSettingsController();
            $currentSprintNumber = $sprintSettingsController->getCurrentSprintNumber();
            
            // Filter reports that contain "Sprint {currentSprintNumber}" in the report name
            $query->where('report_name', 'like', "Sprint {$currentSprintNumber}%");
        }
        
        $reports = $query->orderBy('created_at', 'desc')
            ->paginate(10);
        
        // Pass the filter to the view for maintaining state in pagination links
        return view('saved-reports.index', [
            'reports' => $reports,
            'filter' => $request->filter
        ]);
    }

    /**
     * Show the form for creating a new saved report.
     */
    public function create()
    {
        // Check if there's current report data in the session
        $reportData = [
            'board_id' => session('selected_board', ''),
            'board_name' => session('selected_board_name', ''),
            'story_points_data' => session('current_story_points_data'),
            'bug_cards_data' => session('current_bug_cards_data')
        ];
        
        return view('saved-reports.create', compact('reportData'));
    }

    /**
     * Store a newly created saved report in storage.
     */
    public function store(Request $request)
    {
        // Log the input data for debugging
        \Log::info('SavedReport store input data:', [
            'has_story_points_data' => $request->has('story_points_data'),
            'story_points_data_length' => $request->input('story_points_data') ? strlen($request->input('story_points_data')) : 0,
            'has_bug_cards_data' => $request->has('bug_cards_data'),
            'bug_cards_data_length' => $request->input('bug_cards_data') ? strlen($request->input('bug_cards_data')) : 0,
            'all_fields' => array_keys($request->all())
        ]);
        
        $validated = $request->validate([
            'report_name' => 'required|string|max:255',
            'board_id' => 'required|string',
            'board_name' => 'required|string',
            'notes' => 'nullable|string',
            'story_points_data' => 'nullable|string',
            'bug_cards_data' => 'nullable|string',
        ]);

        $validated['user_id'] = Auth::id();
        
        // Use DB transaction to ensure both SavedReport and SprintReport are created or neither
        DB::beginTransaction();
        
        try {
            // Fix for JSON data handling - ensure proper JSON encoding
            // Check if story_points_data and bug_cards_data are already valid JSON, 
            // if not, encode them properly
            if (isset($validated['story_points_data']) && !empty($validated['story_points_data'])) {
                if (is_string($validated['story_points_data'])) {
                    // Check if it's already a valid JSON string
                    json_decode($validated['story_points_data']);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        // It's not valid JSON, so encode it
                        $validated['story_points_data'] = json_encode([
                            'summary' => [],
                            'teamMembers' => [],
                            'totals' => []
                        ]);
                        \Log::warning('Invalid story_points_data format - using default');
                    }
                }
            }
            
            if (isset($validated['bug_cards_data']) && !empty($validated['bug_cards_data'])) {
                if (is_string($validated['bug_cards_data'])) {
                    // Check if it's already a valid JSON string
                    json_decode($validated['bug_cards_data']);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        // It's not valid JSON, so encode it
                        $validated['bug_cards_data'] = json_encode([
                            'bugCards' => [],
                            'bugCount' => '0 bugs',
                            'totalBugPoints' => 0
                        ]);
                        \Log::warning('Invalid bug_cards_data format - using default');
                    }
                }
            }
            
            // Create the SavedReport
            $savedReport = SavedReport::create($validated);
            
            // Log the created report for debugging
            \Log::info('SavedReport created:', [
                'id' => $savedReport->id,
                'has_story_points_data' => !empty($savedReport->story_points_data),
                'story_points_data_length' => $savedReport->story_points_data ? strlen($savedReport->story_points_data) : 0,
                'has_bug_cards_data' => !empty($savedReport->bug_cards_data),
                'bug_cards_data_length' => $savedReport->bug_cards_data ? strlen($savedReport->bug_cards_data) : 0
            ]);
            
            // Also add this report to the current sprint
            $currentSprint = \App\Models\Sprint::getCurrentSprint();
            
            if ($currentSprint) {
                // Ensure we have valid JSON data
                $storyPointsData = $savedReport->story_points_data;
                $bugCardsData = $savedReport->bug_cards_data;
                
                // Ensure data is in the right format for the SprintReport model
                if (is_string($storyPointsData)) {
                    // Check if it's valid JSON
                    json_decode($storyPointsData);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $storyPointsData = json_encode([
                            'summary' => [],
                            'teamMembers' => [],
                            'totals' => []
                        ]);
                    }
                } else if (is_array($storyPointsData)) {
                    $storyPointsData = json_encode($storyPointsData);
                }
                
                if (is_string($bugCardsData)) {
                    // Check if it's valid JSON
                    json_decode($bugCardsData);
                    if (json_last_error() !== JSON_ERROR_NONE) {
                        $bugCardsData = json_encode([
                            'bugCards' => [],
                            'bugCount' => '0 bugs',
                            'totalBugPoints' => 0
                        ]);
                    }
                } else if (is_array($bugCardsData)) {
                    $bugCardsData = json_encode($bugCardsData);
                }
                
                \App\Models\SprintReport::create([
                    'sprint_id' => $currentSprint->id,
                    'user_id' => Auth::id(),
                    'board_id' => $savedReport->board_id,
                    'board_name' => $savedReport->board_name,
                    'report_name' => $savedReport->report_name,
                    'notes' => $savedReport->notes,
                    'story_points_data' => $storyPointsData,
                    'bug_cards_data' => $bugCardsData,
                    'is_auto_generated' => false,
                ]);
                
                \Log::info('SprintReport created for saved report', [
                    'saved_report_id' => $savedReport->id,
                    'sprint_id' => $currentSprint->id,
                    'story_points_data_type' => gettype($storyPointsData),
                    'story_points_data_length' => is_string($storyPointsData) ? strlen($storyPointsData) : 'not a string',
                    'bug_cards_data_type' => gettype($bugCardsData),
                    'bug_cards_data_length' => is_string($bugCardsData) ? strlen($bugCardsData) : 'not a string',
                    'story_points_valid_json' => json_decode($storyPointsData) ? 'yes' : 'no: ' . json_last_error_msg(),
                    'bug_cards_valid_json' => json_decode($bugCardsData) ? 'yes' : 'no: ' . json_last_error_msg()
                ]);
            } else {
                \Log::warning('No current sprint found, sprint report not created');
            }
            
            DB::commit();
            
            return redirect()->route('saved-reports.index')
                ->with('success', 'Report saved successfully and added to current sprint');
        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Error creating report: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error saving report: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified saved report.
     */
    public function show(SavedReport $savedReport)
    {
        // Make sure the user owns this report
        if ($savedReport->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('saved-reports.show', compact('savedReport'));
    }

    /**
     * Show the form for editing the specified saved report.
     */
    public function edit(SavedReport $savedReport)
    {
        // Make sure the user owns this report
        if ($savedReport->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('saved-reports.edit', compact('savedReport'));
    }

    /**
     * Update the specified saved report in storage.
     */
    public function update(Request $request, SavedReport $savedReport)
    {
        // Make sure the user owns this report
        if ($savedReport->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        $validated = $request->validate([
            'report_name' => 'required|string|max:255',
            'board_name' => 'nullable|string',
            'notes' => 'nullable|string',
            'story_points_data' => 'nullable|string',
            'bug_cards_data' => 'nullable|string',
        ]);
        
        // Make sure we don't lose existing data if not provided in the request
        if (!isset($validated['story_points_data']) && $savedReport->story_points_data) {
            $validated['story_points_data'] = $savedReport->story_points_data;
        }
        
        if (!isset($validated['bug_cards_data']) && $savedReport->bug_cards_data) {
            $validated['bug_cards_data'] = $savedReport->bug_cards_data;
        }
        
        $savedReport->update($validated);
        
        return redirect()->route('saved-reports.show', $savedReport)
            ->with('success', 'Report updated successfully.');
    }

    /**
     * Remove the specified saved report from storage.
     */
    public function destroy(SavedReport $savedReport)
    {
        // Make sure the user owns this report
        if ($savedReport->user_id !== Auth::id() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        $savedReport->delete();
        
        return redirect()->route('saved-reports.index')
            ->with('success', 'Report deleted successfully.');
    }
}
