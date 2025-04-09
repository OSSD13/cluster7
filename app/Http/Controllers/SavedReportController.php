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
            'all_fields' => array_keys($request->all()),
            'is_ajax' => $request->ajax(),
            'content_type' => $request->header('Content-Type')
        ]);
        
        try {
            $validated = $request->validate([
                'report_name' => 'required|string|max:255',
                'name' => 'required|string|max:255',
                'board_id' => 'required|string',
                'board_name' => 'required|string',
                'notes' => 'nullable|string',
                'story_points_data' => 'nullable',
                'bug_cards_data' => 'nullable',
            ]);
    
            // Set name from report_name if not provided
            if (!isset($validated['name']) && isset($validated['report_name'])) {
                $validated['name'] = $validated['report_name'];
            }
    
            // Prepare the report_data field from the validated data
            $reportData = [
                'board_id' => $validated['board_id'],
                'board_name' => $validated['board_name'],
                'report_name' => $validated['report_name'],
                'notes' => $validated['notes'] ?? null
            ];
    
            // Add story points data if available
            if (isset($validated['story_points_data']) && !empty($validated['story_points_data'])) {
                $reportData['story_points_data'] = $validated['story_points_data'];
            }
            
            // Add bug cards data if available
            if (isset($validated['bug_cards_data']) && !empty($validated['bug_cards_data'])) {
                $reportData['bug_cards_data'] = $validated['bug_cards_data'];
            }
    
            $validated['user_id'] = Auth::id();
            $validated['report_data'] = $reportData;
            
            // Remove fields that don't exist in the model
            unset($validated['report_name']);
            unset($validated['board_name']);
            unset($validated['board_id']);
            unset($validated['notes']);
            unset($validated['story_points_data']);
            unset($validated['bug_cards_data']);
            
            // Use DB transaction to ensure both SavedReport and SprintReport are created or neither
            DB::beginTransaction();
            
            try {
                // Create the SavedReport
                $savedReport = SavedReport::create($validated);
                
                // Log the created report for debugging
                \Log::info('SavedReport created:', [
                    'id' => $savedReport->id,
                    'has_report_data' => !empty($savedReport->report_data),
                    'report_data_keys' => is_array($savedReport->report_data) ? array_keys($savedReport->report_data) : 'not an array'
                ]);
                
                // Also add this report to the current sprint
                $currentSprint = \App\Models\Sprint::getCurrentSprint();
                
                if ($currentSprint) {
                    // Extract data from saved report for the sprint report
                    $storyPointsData = null;
                    $bugCardsData = null;
                    
                    if (isset($savedReport->report_data['story_points_data'])) {
                        $storyPointsData = $savedReport->report_data['story_points_data'];
                        // Decode the JSON string if it's a string, to prevent double encoding
                        if (is_string($storyPointsData)) {
                            try {
                                $decoded = json_decode($storyPointsData, true);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    $storyPointsData = $decoded;
                                } else {
                                    \Log::error('Error decoding story_points_data JSON: ' . json_last_error_msg());
                                }
                            } catch (\Exception $e) {
                                \Log::error('Exception decoding story_points_data: ' . $e->getMessage());
                            }
                        }
                    }
                    
                    if (isset($savedReport->report_data['bug_cards_data'])) {
                        $bugCardsData = $savedReport->report_data['bug_cards_data'];
                        // Decode the JSON string if it's a string, to prevent double encoding
                        if (is_string($bugCardsData)) {
                            try {
                                $decoded = json_decode($bugCardsData, true);
                                if (json_last_error() === JSON_ERROR_NONE) {
                                    $bugCardsData = $decoded;
                                } else {
                                    \Log::error('Error decoding bug_cards_data JSON: ' . json_last_error_msg());
                                }
                            } catch (\Exception $e) {
                                \Log::error('Exception decoding bug_cards_data: ' . $e->getMessage());
                            }
                        }
                    }
                    
                    $sprintReport = \App\Models\SprintReport::create([
                        'sprint_id' => $currentSprint->id,
                        'user_id' => Auth::id(),
                        'board_id' => $savedReport->report_data['board_id'] ?? $validated['board_id'],
                        'board_name' => $savedReport->report_data['board_name'] ?? $validated['board_name'],
                        'report_name' => $savedReport->report_data['report_name'] ?? $savedReport->name,
                        'notes' => $savedReport->report_data['notes'] ?? $validated['notes'] ?? null,
                        'story_points_data' => $storyPointsData,
                        'bug_cards_data' => $bugCardsData,
                        'is_auto_generated' => false,
                    ]);
                    
                    \Log::info('SprintReport created for saved report', [
                        'saved_report_id' => $savedReport->id,
                        'sprint_id' => $currentSprint->id,
                        'sprint_report_id' => $sprintReport->id,
                        'story_points_data_type' => gettype($storyPointsData),
                        'bug_cards_data_type' => gettype($bugCardsData)
                    ]);
                } else {
                    \Log::warning('No current sprint found, sprint report not created');
                }
                
                DB::commit();
                
                // Check if this is an AJAX request
                if ($request->ajax() || $request->header('Content-Type') === 'application/json') {
                    return response()->json([
                        'success' => true,
                        'message' => 'Report saved successfully and added to current sprint',
                        'redirect' => route('saved-reports.index')
                    ]);
                }
                
                // Use direct routing instead of redirect
                $reports = SavedReport::where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->paginate(10);
                    
                return view('saved-reports.index', [
                    'reports' => $reports,
                    'success' => 'Report saved successfully and added to current sprint'
                ]);
            } catch (\Exception $e) {
                DB::rollBack();
                
                \Log::error('Error creating report: ' . $e->getMessage(), [
                    'exception' => $e,
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Check if this is an AJAX request
                if ($request->ajax() || $request->header('Content-Type') === 'application/json') {
                    return response()->json([
                        'success' => false,
                        'error' => 'Error saving report: ' . $e->getMessage()
                    ], 422);
                }
                
                // Use direct routing instead of redirect()->back()
                $reportData = [
                    'board_id' => $request->input('board_id', ''),
                    'board_name' => $request->input('board_name', ''),
                    'report_name' => $request->input('report_name', ''),
                    'notes' => $request->input('notes', ''),
                    'story_points_data' => $request->input('story_points_data'),
                    'bug_cards_data' => $request->input('bug_cards_data')
                ];
                
                return view('saved-reports.create', [
                    'reportData' => $reportData,
                    'error' => 'Error saving report: ' . $e->getMessage()
                ]);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('Validation error when saving report: ' . json_encode($e->errors()));
            
            if ($request->ajax() || $request->header('Content-Type') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'error' => 'Validation error',
                    'errors' => $e->errors()
                ], 422);
            }
            
            throw $e;
        } catch (\Exception $e) {
            \Log::error('General error when processing report: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->ajax() || $request->header('Content-Type') === 'application/json') {
                return response()->json([
                    'success' => false,
                    'error' => 'Error processing report: ' . $e->getMessage()
                ], 500);
            }
            
            throw $e;
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
            'name' => 'required|string|max:255',
            'board_name' => 'nullable|string',
            'notes' => 'nullable|string',
            'story_points_data' => 'nullable|string',
            'bug_cards_data' => 'nullable|string',
        ]);
        
        // Make sure we don't lose existing data if not provided in the request
        if (!isset($validated['name']) && isset($validated['report_name'])) {
            $validated['name'] = $validated['report_name'];
        }
        
        // Prepare the report_data field from the validated data and existing data
        $reportData = is_array($savedReport->report_data) ? $savedReport->report_data : [];
        
        // Update with new values
        $reportData['report_name'] = $validated['report_name'];
        $reportData['board_name'] = $validated['board_name'] ?? $reportData['board_name'] ?? null;
        $reportData['notes'] = $validated['notes'] ?? $reportData['notes'] ?? null;
        
        // Update story points data if available
        if (isset($validated['story_points_data']) && !empty($validated['story_points_data'])) {
            $reportData['story_points_data'] = $validated['story_points_data'];
        }
        
        // Update bug cards data if available
        if (isset($validated['bug_cards_data']) && !empty($validated['bug_cards_data'])) {
            $reportData['bug_cards_data'] = $validated['bug_cards_data'];
        }
        
        // Set the report_data field
        $validated['report_data'] = $reportData;
        
        // Remove fields that don't exist in the model
        unset($validated['report_name']);
        unset($validated['board_name']);
        unset($validated['board_id']);
        unset($validated['notes']);
        unset($validated['story_points_data']);
        unset($validated['bug_cards_data']);
        
        $savedReport->update($validated);
        
        // Use direct routing instead of redirect
        return view('saved-reports.show', [
            'savedReport' => $savedReport,
            'success' => 'Report updated successfully.'
        ]);
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
        
        // Use direct routing instead of redirect
        $reports = SavedReport::where('user_id', Auth::id())
            ->orderBy('created_at', 'desc')
            ->paginate(10);
            
        return view('saved-reports.index', [
            'reports' => $reports,
            'success' => 'Report deleted successfully.'
        ]);
    }
}
