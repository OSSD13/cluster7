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
                        'redirect' => route('saved-reports.index'),
                        'savedReportId' => $savedReport->id
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

    /**
     * Export the saved report using the template view.
     *
     * @param SavedReport $savedReport
     * @return \Illuminate\Http\Response
     */
    public function exportTemplate(SavedReport $savedReport)
    {
        // Verify ownership
        if ($savedReport->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403, 'Unauthorized action.');
        }

        // Prepare the report data for the template
        $reportData = $savedReport->report_data;

        // Log report data for debugging
        \Log::info('exportTemplate report data:', $reportData);

        // Decode JSON data if stored as strings
        $storyPointsData = null;
        $bugCardsData = null;
        $memberPointsData = null;

        if (isset($reportData['story_points_data'])) {
            $storyPointsData = $reportData['story_points_data'];
            if (is_string($storyPointsData)) {
                $storyPointsData = json_decode($storyPointsData, true);
            }
        }

        if (isset($reportData['bug_cards_data'])) {
            $bugCardsData = $reportData['bug_cards_data'];
            if (is_string($bugCardsData)) {
                $bugCardsData = json_decode($bugCardsData, true);
            }
        }

        if (isset($reportData['member_points_data'])) {
            $memberPointsData = $reportData['member_points_data'];
            if (is_string($memberPointsData)) {
                $memberPointsData = json_decode($memberPointsData, true);
            }
        }

        // Extract developer data and map fields correctly
        $developers = [];

        // If we have member points data from the report, use it
        if (!empty($memberPointsData) && is_array($memberPointsData)) {
            foreach ($memberPointsData as $member) {
                $developers[] = (object)[
                    'name' => $member['fullName'] ?? $member['username'] ?? 'Unknown',
                    'point_personal' => $member['pointPersonal'] ?? 0,
                    'test_pass' => $member['passPoint'] ?? 0,
                    'bug' => $member['bugPoint'] ?? 0,
                    'final_pass_point' => $member['finalPoint'] ?? 0,
                    'cancel' => $member['cancelPoint'] ?? 0,
                    'sum_final' => $member['finalPoint'] ?? 0,
                    'remark' => '',
                    'day_off' => 'No'
                ];
            }
        } else {
            // If no specific member points data, use the formatDevelopersData method
            $developers = $this->formatDevelopersData([
                'board_name' => $reportData['board_name'] ?? 'Development Team',
                'bug_cards_data' => $bugCardsData,
                'story_points_data' => $storyPointsData
            ]);
        }

        // Calculate sums for the totals row
        $sumPointPersonal = 0;
        $sumTestPass = 0;
        $sumBug = 0;
        $sumFinalPassPoint = 0;
        $sumCancel = 0;
        $sumFinal = 0;

        foreach ($developers as $dev) {
            $sumPointPersonal += $dev->point_personal;
            $sumTestPass += $dev->test_pass;
            $sumBug += $dev->bug;
            $sumFinalPassPoint += $dev->final_pass_point;
            $sumCancel += $dev->cancel;
            $sumFinal += $dev->sum_final;
        }

        // Format the report for the template - directly use values from storyPointsData with fallbacks
        $report = [
            'author' => auth()->user()->name,
            'date_start' => $reportData['date_start'] ?? now()->format('Y-m-d'),
            'date_finish' => $reportData['date_finish'] ?? now()->addDays(7)->format('Y-m-d'),
            'sprint' => $reportData['sprint'] ?? 'Current Sprint',
            'last_update' => $reportData['last_update'] ?? now()->format('Y-m-d H:i'),
            'team_name' => $reportData['board_name'] ?? 'Development Team',

            // For plan_point: use planPoints from data or fallback to sum of personal points
            'plan_point' => $storyPointsData['plan_point'] ?? $storyPointsData['planPoints'] ?? 
                $storyPointsData['summary']['planPoints'] ?? $sumPointPersonal,
            'actual_point' => $storyPointsData['actual_point'] ?? $storyPointsData['actualPoints'] ??
                $storyPointsData['summary']['actualPoints'] ?? $sumTestPass,
            'remain' => $storyPointsData['remain'] ?? $storyPointsData['remainPercent'] ??
                $storyPointsData['summary']['remainPercent'] ?? '0%',
            'percent' => $storyPointsData['percent'] ?? $storyPointsData['percentComplete'] ?? 
                $storyPointsData['summary']['percentComplete'] ?? '0%',
            'current_sprint_point' => $storyPointsData['current_sprint_point'] ?? 
                $storyPointsData['summary']['currentSprintPoints'] ?? $sumPointPersonal,
            'current_sprint_actual_point' => $storyPointsData['current_sprint_actual_point'] ?? 
                $storyPointsData['summary']['actualCurrentSprint'] ?? $sumFinal,

            'developers' => $developers,
            'backlog' => $this->formatBacklogData([
                'board_name' => $reportData['board_name'] ?? 'Development Team',
                'bug_cards_data' => $bugCardsData,
                'story_points_data' => $storyPointsData
            ]),
            'logo' => env('APP_URL') . '/images/logo.png',
            'sum_point_personal' => $sumPointPersonal,
            'sum_test_pass' => $sumTestPass,
            'sum_bug' => $sumBug,
            'sum_final_pass_point' => $sumFinalPassPoint,
            'sum_cancel' => $sumCancel,
            'sum_final' => $sumFinal
        ];

        // Log the plan point values for debugging
        \Log::info('Plan Point Values:', [
            'storyPointsData_plan_point' => $storyPointsData['plan_point'] ?? 'not found',
            'storyPointsData_planPoints' => $storyPointsData['planPoints'] ?? 'not found',
            'storyPointsData_summary_planPoints' => $storyPointsData['summary']['planPoints'] ?? 'not found',
            'sumPointPersonal' => $sumPointPersonal,
            'final_plan_point_value' => $report['plan_point']
        ]);

        // Convert the report to an object to match the expected structure in the template
        $reportObject = json_decode(json_encode($report));

        // Render the template view
        return view('saved-reports.template', ['report' => $reportObject]);
    }

    /**
     * Export current report data directly to CSV template without saving first.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportToCsv(Request $request)
    {
        // Log the input data for debugging
        \Log::info('ExportToCsv input data:', $request->all());

        // Extract data from request
        $storyPointsData = $request->input('story_points_data');
        if (is_string($storyPointsData)) {
            $storyPointsData = json_decode($storyPointsData, true);
        }

        // Log decoded story points data for debugging
        \Log::info('Decoded story points data:', $storyPointsData ?? []);

        $bugCardsData = $request->input('bug_cards_data');
        if (is_string($bugCardsData)) {
            $bugCardsData = json_decode($bugCardsData, true);
        }

        // Get memberPoints data from cachedData if available
        $memberPointsData = [];

        if ($request->has('member_points_data')) {
            $memberPointsData = $request->input('member_points_data');
            if (is_string($memberPointsData)) {
                $memberPointsData = json_decode($memberPointsData, true);
            }
        }

        // Extract developer data and map fields correctly
        $developers = [];

        // If we have member points data from the report, use it
        if (!empty($memberPointsData) && is_array($memberPointsData)) {
            foreach ($memberPointsData as $member) {
                $developers[] = (object)[
                    'name' => $member['fullName'] ?? $member['username'] ?? 'Unknown',
                    'point_personal' => $member['pointPersonal'] ?? 0,
                    'test_pass' => $member['passPoint'] ?? 0,
                    'bug' => $member['bugPoint'] ?? 0,
                    'final_pass_point' => $member['finalPoint'] ?? 0,
                    'cancel' => $member['cancelPoint'] ?? 0,
                    'sum_final' => $member['finalPoint'] ?? 0,
                    'remark' => '',
                    'day_off' => 'No'
                ];
            }
        } else {
            // If no specific member points data, use the formatDevelopersData method
            $developers = $this->formatDevelopersData([
                'board_name' => $request->input('board_name', 'Development Team'),
                'bug_cards_data' => $bugCardsData,
                'story_points_data' => $storyPointsData
            ]);
        }

        // Calculate sums for the totals row
        $sumPointPersonal = 0;
        $sumTestPass = 0;
        $sumBug = 0;
        $sumFinalPassPoint = 0;
        $sumCancel = 0;
        $sumFinal = 0;

        foreach ($developers as $dev) {
            $sumPointPersonal += $dev->point_personal;
            $sumTestPass += $dev->test_pass;
            $sumBug += $dev->bug;
            $sumFinalPassPoint += $dev->final_pass_point;
            $sumCancel += $dev->cancel;
            $sumFinal += $dev->sum_final;
        }

        // Format the report for the template - directly use values from storyPointsData
        $report = [
            'author' => auth()->user()->name,
            'date_start' => now()->format('Y-m-d'),
            'date_finish' => now()->addDays(7)->format('Y-m-d'),
            'sprint' => $request->input('sprint', 'Current Sprint'),
            'last_update' => now()->format('Y-m-d H:i'),
            'team_name' => $request->input('board_name', 'Development Team'),

            // For plan_point: use planPoints from data or fallback to sum of personal points
            'plan_point' => $storyPointsData['plan_point'] ?? $storyPointsData['planPoints'] ?? 
                $storyPointsData['summary']['planPoints'] ?? $sumPointPersonal,
            'actual_point' => $storyPointsData['actual_point'] ?? $storyPointsData['actualPoints'] ??
                $storyPointsData['summary']['actualPoints'] ?? $sumTestPass,
            'remain' => $storyPointsData['remain'] ?? $storyPointsData['remainPercent'] ??
                $storyPointsData['summary']['remainPercent'] ?? '0%',
            'percent' => $storyPointsData['percent'] ?? $storyPointsData['percentComplete'] ?? 
                $storyPointsData['summary']['percentComplete'] ?? '0%',
            'current_sprint_point' => $storyPointsData['current_sprint_point'] ?? 
                $storyPointsData['summary']['currentSprintPoints'] ?? $sumPointPersonal,
            'current_sprint_actual_point' => $storyPointsData['current_sprint_actual_point'] ?? 
                $storyPointsData['summary']['actualCurrentSprint'] ?? $sumFinal,

            'developers' => $developers,
            'backlog' => $this->formatBacklogData([
                'board_name' => $request->input('board_name', 'Development Team'),
                'bug_cards_data' => $bugCardsData,
                'story_points_data' => $storyPointsData
            ]),
            'logo' => env('APP_URL') . '/images/logo.png',
            'sum_point_personal' => $sumPointPersonal,
            'sum_test_pass' => $sumTestPass,
            'sum_bug' => $sumBug,
            'sum_final_pass_point' => $sumFinalPassPoint,
            'sum_cancel' => $sumCancel,
            'sum_final' => $sumFinal
        ];

        // Process extra points data if available
        if ($request->has('extra_points_data')) {
            $extraPointsData = $request->input('extra_points_data');
            if (is_string($extraPointsData)) {
                $extraPointsData = json_decode($extraPointsData, true);
            }

            if (is_array($extraPointsData) && !empty($extraPointsData)) {
                // Log the raw extra points data
                \Log::info('Raw extra points data:', $extraPointsData);

                // Convert each item to an object and ensure proper structure
                $report['extra_points'] = array_map(function($item) {
                    return (object) [
                        'extra_personal' => $item['extra_personal'] ?? 'Unknown',
                        'extra_point' => floatval($item['extra_point'] ?? 0)
                    ];
                }, $extraPointsData);

                // Log the processed extra points data
                \Log::info('Processed extra points data:', $report['extra_points']);
            }
        }

        // Log the plan point values for debugging
        \Log::info('Plan Point Values:', [
            'storyPointsData_plan_point' => $storyPointsData['plan_point'] ?? 'not found',
            'storyPointsData_planPoints' => $storyPointsData['planPoints'] ?? 'not found',
            'storyPointsData_summary_planPoints' => $storyPointsData['summary']['planPoints'] ?? 'not found',
            'sumPointPersonal' => $sumPointPersonal,
            'final_plan_point_value' => $report['plan_point']
        ]);

        // Convert the report to an object to match the expected structure
        $reportObject = json_decode(json_encode($report));

        // Check if autoprint parameter exists in the request
        $autoprint = $request->input('autoprint', false);

        // Render the template view with autoprint parameter if needed
        return view('saved-reports.template', [
            'report' => $reportObject,
            'autoprint' => $autoprint
        ]);
    }

    /**
     * Format developers data for the template.
     *
     * @param array $reportData
     * @return array
     */
    private function formatDevelopersData($reportData)
    {
        $developers = [];

        // If we have developers data in the report, use it
        if (isset($reportData['developers']) && is_array($reportData['developers'])) {
            // Convert each array item to an object if it's not already
            $devObjects = [];
            foreach ($reportData['developers'] as $dev) {
                $devObjects[] = is_array($dev) ? (object)$dev : $dev;
            }
            return $devObjects;
        }

        // Otherwise, create sample data from bug cards if available
        if (isset($reportData['bug_cards_data']) && is_array($reportData['bug_cards_data'])) {
            $devMap = [];

            // Process each list in bug cards data
            foreach ($reportData['bug_cards_data'] as $list => $listData) {
                // Check if this is a list with cards
                if (isset($listData['cards']) && is_array($listData['cards'])) {
                    foreach ($listData['cards'] as $card) {
                        // Check if card has members
                        if (isset($card['members']) && is_array($card['members'])) {
                            foreach ($card['members'] as $member) {
                                // Get member name
                                $memberName = $member['fullName'] ?? $member['username'] ?? 'Unknown';

                                if (!isset($devMap[$memberName])) {
                                    $devMap[$memberName] = [
                                        'name' => $memberName,
                                        'point_personal' => 0,
                                        'test_pass' => 0,
                                        'bug' => 0,
                                        'final_pass_point' => 0,
                                        'cancel' => 0,
                                        'sum_final' => 0,
                                        'remark' => '',
                                        'day_off' => 'No'
                                    ];
                                }

                                // Count the bug
                                $devMap[$memberName]['bug']++;
                            }
                        } else {
                            // If no members assigned, count as 'Unassigned'
                            if (!isset($devMap['Unassigned'])) {
                                $devMap['Unassigned'] = [
                                    'name' => 'Unassigned',
                                    'point_personal' => 0,
                                    'test_pass' => 0,
                                    'bug' => 0,
                                    'final_pass_point' => 0,
                                    'cancel' => 0,
                                    'sum_final' => 0,
                                    'remark' => '',
                                    'day_off' => 'No'
                                ];
                            }
                            $devMap['Unassigned']['bug']++;
                        }
                    }
                }
            }

            // Convert all developer arrays to objects
            foreach ($devMap as $name => $data) {
                $developers[] = (object)$data;
            }
        }

        // If still empty, return at least one developer
        if (empty($developers)) {
            $developers[] = (object)[
                'name' => auth()->user()->name,
                'point_personal' => 0,
                'test_pass' => 0,
                'bug' => 0,
                'final_pass_point' => 0,
                'cancel' => 0,
                'sum_final' => 0,
                'remark' => '',
                'day_off' => 'No'
            ];
        }

        return $developers;
    }

    /**
     * Format backlog data for the template.
     *
     * @param array $reportData
     * @return array
     */
    private function formatBacklogData($reportData)
    {
        $backlog = [];
        $teamName = $reportData['board_name'] ?? 'Development Team';
        
        // ดึงข้อมูล backlog จาก BacklogController
        try {
            $backlogController = new \App\Http\Controllers\BacklogController(app(\App\Services\TrelloService::class));
            $backlogData = $backlogController->getBacklogData();
            
            if (!empty($backlogData['allBugs'])) {
                // กรองเฉพาะข้อมูลของทีมที่ต้องการ
                $filteredBugs = $backlogData['allBugs']->filter(function($bug) use ($teamName) {
                    return (!isset($bug['team']) || $bug['team'] == $teamName);
                });
                
                // จัดกลุ่มตาม member และ sprint
                $memberBacklogCards = [];
                
                foreach ($filteredBugs as $bug) {
                    // ถ้ามีข้อมูล member
                    if (isset($bug['members']) && !empty($bug['members'])) {
                        foreach ($bug['members'] as $member) {
                            $memberName = $member['fullName'] ?? $member['username'] ?? 'Unknown';
                            $sprintOrigin = $bug['sprint_origin'] ?? $bug['sprint_number'] ?? 'Current';
                            
                            // สร้าง key สำหรับการจัดกลุ่ม
                            $key = $memberName . '_' . $sprintOrigin;
                            
                            if (!isset($memberBacklogCards[$key])) {
                                $memberBacklogCards[$key] = [
                                    'sprint' => $sprintOrigin,
                                    'personal' => $memberName,
                                    'point_all' => 0,
                                    'test_pass' => 0,
                                    'bug' => 0,
                                    'cancel' => 0
                                ];
                            }
                            
                            // เพิ่ม point
                            $memberBacklogCards[$key]['point_all'] += floatval($bug['points'] ?? 0);
                        }
                    }
                    // ถ้าไม่มีข้อมูล member แต่มี assignee
                    else if (isset($bug['assignee']) && !empty($bug['assignee'])) {
                        $memberName = $bug['assignee'];
                        $sprintOrigin = $bug['sprint_origin'] ?? $bug['sprint_number'] ?? 'Current';
                        
                        // สร้าง key สำหรับการจัดกลุ่ม
                        $key = $memberName . '_' . $sprintOrigin;
                        
                        if (!isset($memberBacklogCards[$key])) {
                            $memberBacklogCards[$key] = [
                                'sprint' => $sprintOrigin,
                                'personal' => $memberName,
                                'point_all' => 0,
                                'test_pass' => 0,
                                'bug' => 0,
                                'cancel' => 0
                            ];
                        }
                        
                        // เพิ่ม point
                        $memberBacklogCards[$key]['point_all'] += floatval($bug['points'] ?? 0);
                    }
                    // ถ้าไม่มีทั้ง member และ assignee ให้ใส่เป็น 'Unassigned'
                    else {
                        $memberName = 'Unassigned';
                        $sprintOrigin = $bug['sprint_origin'] ?? $bug['sprint_number'] ?? 'Current';
                        
                        // สร้าง key สำหรับการจัดกลุ่ม
                        $key = $memberName . '_' . $sprintOrigin;
                        
                        if (!isset($memberBacklogCards[$key])) {
                            $memberBacklogCards[$key] = [
                                'sprint' => $sprintOrigin,
                                'personal' => $memberName,
                                'point_all' => 0,
                                'test_pass' => 0,
                                'bug' => 0,
                                'cancel' => 0
                            ];
                        }
                        
                        // เพิ่ม point
                        $memberBacklogCards[$key]['point_all'] += floatval($bug['points'] ?? 0);
                    }
                }
                
                // แปลงข้อมูลจาก associative array เป็น indexed array
                foreach ($memberBacklogCards as $backlogData) {
                    $backlog[] = (object) $backlogData;
                }
            }
        } catch (\Exception $e) {
            // บันทึก log ในกรณีเกิดข้อผิดพลาด
            \Log::error('Error getting backlog data: ' . $e->getMessage());
            
            // ถ้าเกิดข้อผิดพลาดในการดึงข้อมูลจาก BacklogController ให้ใช้วิธีการเดิม
            // ตรวจสอบว่ามีข้อมูล bug_cards_data หรือไม่
            if (isset($reportData['bug_cards_data']) && !empty($reportData['bug_cards_data'])) {
                $bugCardsData = $reportData['bug_cards_data'];
                
                // แปลงจาก JSON string เป็น array ถ้าจำเป็น
                if (is_string($bugCardsData)) {
                    $bugCardsData = json_decode($bugCardsData, true);
                }
                
                // วิธีที่ 1: ถ้ามีข้อมูล listsData ใน bug_cards_data
                if (isset($bugCardsData['listsData']) && !empty($bugCardsData['listsData'])) {
                    $listsData = $bugCardsData['listsData'];
                    $memberBacklogCards = [];
                    
                    // วนลูปผ่านทุก list
                    foreach ($listsData as $listName => $listData) {
                        if (isset($listData['cards']) && !empty($listData['cards'])) {
                            foreach ($listData['cards'] as $card) {
                                // ตรวจสอบว่าเป็น card ของทีมที่ต้องการหรือไม่
                                if ((!isset($card['team']) || $card['team'] == $teamName) && 
                                    isset($card['members']) && !empty($card['members']) && 
                                    isset($card['points']) && $card['points'] > 0) {
                                    
                                    // จัดกลุ่ม card ตาม member
                                    foreach ($card['members'] as $member) {
                                        $memberName = $member['fullName'] ?? $member['username'] ?? 'Unknown';
                                        $sprintOrigin = $card['sprint_origin'] ?? $card['sprint_number'] ?? 'Current';
                                        
                                        // สร้าง key สำหรับการจัดกลุ่ม (member + sprint)
                                        $key = $memberName . '_' . $sprintOrigin;
                                        
                                        if (!isset($memberBacklogCards[$key])) {
                                            $memberBacklogCards[$key] = [
                                                'sprint' => $sprintOrigin,
                                                'personal' => $memberName,
                                                'point_all' => 0,
                                                'test_pass' => 0,
                                                'bug' => 0,
                                                'cancel' => 0
                                            ];
                                        }
                                        
                                        // เพิ่ม point
                                        $memberBacklogCards[$key]['point_all'] += floatval($card['points']);
                                    }
                                }
                            }
                        }
                    }
                    
                    // แปลงข้อมูลจาก associative array เป็น indexed array
                    foreach ($memberBacklogCards as $backlogData) {
                        $backlog[] = (object) $backlogData;
                    }
                }
                // วิธีที่ 2: ถ้ามีข้อมูล allBugs ใน bug_cards_data (รูปแบบของ backlogData)
                else if (isset($bugCardsData['allBugs']) && !empty($bugCardsData['allBugs'])) {
                    $allBugs = $bugCardsData['allBugs'];
                    $memberBacklogCards = [];
                    
                    foreach ($allBugs as $bug) {
                        // ตรวจสอบว่าเป็น bug ของทีมที่ต้องการหรือไม่
                        if ((!isset($bug['team']) || $bug['team'] == $teamName) && 
                            isset($bug['points']) && $bug['points'] > 0) {
                            
                            // ถ้ามีข้อมูล member ใน bug
                            if (isset($bug['members']) && !empty($bug['members'])) {
                                foreach ($bug['members'] as $member) {
                                    $memberName = $member['fullName'] ?? $member['username'] ?? 'Unknown';
                                    $sprintOrigin = $bug['sprint_origin'] ?? $bug['sprint_number'] ?? 'Current';
                                    
                                    // สร้าง key สำหรับการจัดกลุ่ม
                                    $key = $memberName . '_' . $sprintOrigin;
                                    
                                    if (!isset($memberBacklogCards[$key])) {
                                        $memberBacklogCards[$key] = [
                                            'sprint' => $sprintOrigin,
                                            'personal' => $memberName,
                                            'point_all' => 0,
                                            'test_pass' => 0,
                                            'bug' => 0,
                                            'cancel' => 0
                                        ];
                                    }
                                    
                                    // เพิ่ม point
                                    $memberBacklogCards[$key]['point_all'] += floatval($bug['points']);
                                }
                            } 
                            // ถ้าไม่มีข้อมูล member แต่มี assignee
                            else if (isset($bug['assignee']) && !empty($bug['assignee'])) {
                                $memberName = $bug['assignee'];
                                $sprintOrigin = $bug['sprint_origin'] ?? $bug['sprint_number'] ?? 'Current';
                                
                                // สร้าง key สำหรับการจัดกลุ่ม
                                $key = $memberName . '_' . $sprintOrigin;
                                
                                if (!isset($memberBacklogCards[$key])) {
                                    $memberBacklogCards[$key] = [
                                        'sprint' => $sprintOrigin,
                                        'personal' => $memberName,
                                        'point_all' => 0,
                                        'test_pass' => 0,
                                        'bug' => 0,
                                        'cancel' => 0
                                    ];
                                }
                                
                                // เพิ่ม point
                                $memberBacklogCards[$key]['point_all'] += floatval($bug['points']);
                            }
                            // ถ้าไม่มีทั้ง member และ assignee ให้ใส่เป็น 'Unassigned'
                            else {
                                $memberName = 'Unassigned';
                                $sprintOrigin = $bug['sprint_origin'] ?? $bug['sprint_number'] ?? 'Current';
                                
                                // สร้าง key สำหรับการจัดกลุ่ม
                                $key = $memberName . '_' . $sprintOrigin;
                                
                                if (!isset($memberBacklogCards[$key])) {
                                    $memberBacklogCards[$key] = [
                                        'sprint' => $sprintOrigin,
                                        'personal' => $memberName,
                                        'point_all' => 0,
                                        'test_pass' => 0,
                                        'bug' => 0,
                                        'cancel' => 0
                                    ];
                                }
                                
                                // เพิ่ม point
                                $memberBacklogCards[$key]['point_all'] += floatval($bug['points']);
                            }
                        }
                    }
                    
                    // แปลงข้อมูลจาก associative array เป็น indexed array
                    foreach ($memberBacklogCards as $backlogData) {
                        $backlog[] = (object) $backlogData;
                    }
                }
                // วิธีที่ 3: ถ้ามีข้อมูล bugCards ใน bug_cards_data
                else if (isset($bugCardsData['bugCards']) && !empty($bugCardsData['bugCards'])) {
                    $bugCards = $bugCardsData['bugCards'];
                    $memberBacklogCards = [];
                    
                    foreach ($bugCards as $card) {
                        if (isset($card['points']) && $card['points'] > 0) {
                            // สมมติว่า members เป็น string และอาจมีหลาย member คั่นด้วย ,
                            $membersList = isset($card['members']) ? explode(',', str_replace('Members:', '', $card['members'])) : ['Unassigned'];
                            
                            foreach ($membersList as $memberName) {
                                $memberName = trim($memberName);
                                if (empty($memberName)) continue;
                                
                                // สมมติว่า card ไม่มีข้อมูล sprint origin ชัดเจน ใช้ 'Current' เป็นค่าเริ่มต้น
                                $sprintOrigin = 'Current';
                                
                                // สร้าง key สำหรับการจัดกลุ่ม
                                $key = $memberName . '_' . $sprintOrigin;
                                
                                if (!isset($memberBacklogCards[$key])) {
                                    $memberBacklogCards[$key] = [
                                        'sprint' => $sprintOrigin,
                                        'personal' => $memberName,
                                        'point_all' => 0,
                                        'test_pass' => 0,
                                        'bug' => 0,
                                        'cancel' => 0
                                    ];
                                }
                                
                                // เพิ่ม point
                                $memberBacklogCards[$key]['point_all'] += floatval($card['points']);
                            }
                        }
                    }
                    
                    // แปลงข้อมูลจาก associative array เป็น indexed array
                    foreach ($memberBacklogCards as $backlogData) {
                        $backlog[] = (object) $backlogData;
                    }
                }
            }
        }
        
        // ถ้าไม่มีข้อมูล backlog ให้สร้างข้อมูลตัวอย่าง 1 รายการ
        if (empty($backlog)) {
            $backlog[] = (object)[
                'sprint' => 'Current',
                'personal' => 'Unassigned',
                'point_all' => 0,
                'test_pass' => 0, 
                'bug' => 0,
                'cancel' => 0
            ];
        }

        return $backlog;
    }
}
