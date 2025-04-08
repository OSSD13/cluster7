<?php

namespace App\Http\Controllers;

use App\Models\MinorCase;
use App\Models\Sprint;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class MinorCaseController extends Controller
{
    /**
     * Display a listing of the minor cases.
     */
    public function index(Request $request)
    {
        $sprintId = $request->input('sprint_id');
        $boardId = $request->input('board_id');
        $status = $request->input('status');

        $query = MinorCase::with(['user', 'sprint']);

        if ($boardId) {
            $query->where('board_id', $boardId);
        }

        if ($sprintId) {
            $query->where('sprint_id', $sprintId);
        } else {
            // Default to current sprint if no sprint specified
            $currentSprint = Sprint::getCurrentSprint();
            if ($currentSprint) {
                $query->where('sprint_id', $currentSprint->id);
            }
        }

        if ($status) {
            $query->where('status', $status);
        }

        $minorCases = $query->orderBy('created_at', 'desc')->get();

        if ($request->expectsJson()) {
            return response()->json($minorCases);
        }

        $sprints = Sprint::orderBy('sprint_number', 'desc')->get();

        return view('minor-cases.index', [
            'minorCases' => $minorCases,
            'sprints' => $sprints,
            'currentSprintId' => $sprintId ?? ($currentSprint->id ?? null),
            'currentStatus' => $status
        ]);
    }

    /**
     * API endpoint to get minor cases by board ID.
     */
    public function getByBoardId(Request $request, $boardId)
    {
        $minorCases = MinorCase::where('board_id', $boardId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($minorCases);
    }

    /**
     * Show the form for creating a new minor case.
     */
    public function create()
    {
        $sprints = Sprint::orderBy('sprint_number', 'desc')->get();
        $users = User::orderBy('name')->get();
        $currentSprint = Sprint::getCurrentSprint();

        return view('minor-cases.create', [
            'sprints' => $sprints,
            'users' => $users,
            'currentSprint' => $currentSprint
        ]);
    }

    /**
     * Store a newly created minor case in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'board_id' => 'required|string',
            'sprint' => 'required|string',
            'card_detail' => 'required|string|max:255',
            'description' => 'nullable|string',
            'member' => 'required|string|max:255',
            'points' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // Find or create the sprint if needed
        $sprintNumber = $request->input('sprint');
        $sprint = Sprint::where('sprint_number', $sprintNumber)->first();

        if (!$sprint) {
            // If no sprint exists with this number, use the current sprint
            $sprint = Sprint::getCurrentSprint();

            if (!$sprint) {
                return response()->json([
                    'success' => false,
                    'errors' => ['sprint' => ['Invalid sprint number']]
                ], 422);
            }
        }

        $minorCase = new MinorCase();
        $minorCase->board_id = $request->input('board_id');
        $minorCase->sprint_id = $sprint->id;
        $minorCase->title = $request->input('card_detail');
        $minorCase->description = $request->input('description');
        $minorCase->member_name = $request->input('member');
        $minorCase->points = $request->input('points');
        $minorCase->status = 'open';
        $minorCase->user_id = Auth::id();

        $minorCase->save();

        // Format the response to match the expected format in the frontend
        $responseData = [
            'id' => $minorCase->id,
            'board_id' => $minorCase->board_id,
            'sprint' => $sprintNumber,
            'card_detail' => $minorCase->title,
            'description' => $minorCase->description,
            'member' => $minorCase->member_name,
            'points' => (float)$minorCase->points,
            'created_at' => $minorCase->created_at
        ];

        return response()->json([
            'success' => true,
            'data' => $responseData
        ]);
    }

    /**
     * Display the specified minor case.
     */
    public function show(string $id)
    {
        $minorCase = MinorCase::findOrFail($id);

        // Find the sprint number
        $sprint = Sprint::find($minorCase->sprint_id);
        $sprintNumber = $sprint ? $sprint->sprint_number : '';

        // Format the response to match the expected format in the frontend
        $responseData = [
            'id' => $minorCase->id,
            'board_id' => $minorCase->board_id,
            'sprint' => $sprintNumber,
            'card_detail' => $minorCase->title,
            'description' => $minorCase->description,
            'member' => $minorCase->member_name,
            'points' => (float)$minorCase->points,
            'created_at' => $minorCase->created_at
        ];

        return response()->json($responseData);
    }

    /**
     * Show the form for editing the specified minor case.
     */
    public function edit(string $id)
    {
        $minorCase = MinorCase::findOrFail($id);
        $sprints = Sprint::orderBy('sprint_number', 'desc')->get();
        $users = User::orderBy('name')->get();

        return view('minor-cases.edit', [
            'minorCase' => $minorCase,
            'sprints' => $sprints,
            'users' => $users
        ]);
    }

    /**
     * Update the specified minor case in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'board_id' => 'required|string',
            'sprint' => 'required|string',
            'card_detail' => 'required|string|max:255',
            'description' => 'nullable|string',
            'member' => 'required|string|max:255',
            'points' => 'required|numeric|min:0|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $minorCase = MinorCase::findOrFail($id);

        // Find the sprint if needed
        $sprintNumber = $request->input('sprint');
        $sprint = Sprint::where('sprint_number', $sprintNumber)->first();

        if (!$sprint) {
            // If no sprint exists with this number, use the current sprint
            $sprint = Sprint::getCurrentSprint();

            if (!$sprint) {
                return response()->json([
                    'success' => false,
                    'errors' => ['sprint' => ['Invalid sprint number']]
                ], 422);
            }
        }

        $minorCase->board_id = $request->input('board_id');
        $minorCase->sprint_id = $sprint->id;
        $minorCase->title = $request->input('card_detail');
        $minorCase->description = $request->input('description');
        $minorCase->member_name = $request->input('member');
        $minorCase->points = $request->input('points');

        $minorCase->save();

        // Format the response to match the expected format in the frontend
        $responseData = [
            'id' => $minorCase->id,
            'board_id' => $minorCase->board_id,
            'sprint' => $sprintNumber,
            'card_detail' => $minorCase->title,
            'description' => $minorCase->description,
            'member' => $minorCase->member_name,
            'points' => (float)$minorCase->points,
            'created_at' => $minorCase->created_at
        ];

        return response()->json([
            'success' => true,
            'data' => $responseData
        ]);
    }

    /**
     * Remove the specified minor case from storage.
     */
    public function destroy(string $id)
    {
        $minorCase = MinorCase::findOrFail($id);
        $minorCase->delete();

        return response()->json([
            'success' => true,
            'message' => 'Minor case deleted successfully'
        ]);
    }

    /**
     * Get minor cases for a specific sprint (for API).
     */
    public function getBySprintId(Request $request, $sprintId)
    {
        $status = $request->input('status');

        $query = MinorCase::with(['user'])->where('sprint_id', $sprintId);

        if ($status) {
            $query->where('status', $status);
        }

        $minorCases = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $minorCases
        ]);
    }

    /**
     * Update the status of a minor case (for API).
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:open,in-progress,resolved',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $minorCase = MinorCase::findOrFail($id);
        $minorCase->status = $request->input('status');
        $minorCase->save();

        return response()->json([
            'success' => true,
            'data' => $minorCase
        ]);
    }
}
