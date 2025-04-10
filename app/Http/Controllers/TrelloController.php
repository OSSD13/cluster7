<?php
/*
 * TrelloController
 * This controller handles the integration with Trello API for fetching story points and board data.
 * @author : Patyot Sompan 66160109
 * @Create Date : 2025-04-05
 */
namespace App\Http\Controllers;

use App\Http\Controllers\BacklogController;
use App\Models\Setting;
use App\Models\TrelloBoardData;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class TrelloController extends Controller
{
    private function getTrelloCredentials()
    {
        return [
            'apiKey'   => $this->getSetting('trello_api_key'),
            'apiToken' => $this->getSetting('trello_api_token'),
            'boardId'  => $this->getSetting('trello_board_id'),
        ];
    }

    private function getSetting($key)
    {
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : null;
    }

    private function getTrelloApiBaseUrl()
    {
        return 'https://api.trello.com/1/';
    }

    public function fetchStoryPoints(Request $request)
    {
        try {
            // Add debugging to help identify the issue
            \Log::info('Starting fetchStoryPoints', [
                'board_id'    => $request->input('board_id'),
                'user'        => auth()->user()->name,
                'timestamp'   => now()->toDateTimeString(),
                'request_url' => $request->fullUrl(),
                'base_url'    => url('/'),
            ]);

            // Get Trello API credentials from settings
            $credentials = $this->getTrelloCredentials();
            $apiKey      = $credentials['apiKey'];
            $apiToken    = $credentials['apiToken'];

            if (! $apiKey || ! $apiToken) {
                throw new \Exception('Trello API credentials are not configured. Please go to Settings > Trello API Settings to configure them.');
            }

            // Get board ID from request or use default
            $boardId = $request->input('board_id');
            if (! $boardId) {
                $boardId = $credentials['boardId'];
                if (! $boardId) {
                    throw new \Exception('Board ID is required.');
                }
            }

            // Get force refresh parameter from request
            $forceRefresh = $request->input('force_refresh', false);

            // Check if we have cached data for this board
            $cachedData = \App\Models\TrelloBoardData::where('board_id', $boardId)->first();

            // If we have recent cached data and not forcing refresh, return it
            if ($cachedData && ! $forceRefresh && ! $cachedData->isStale()) {
                \Log::info('Using cached data for board ID: ' . $boardId, [
                    'last_fetched_at' => $cachedData->last_fetched_at,
                ]);

                // Return the cached data with the last fetched timestamp
                return response()->json([
                    'storyPoints'      => $cachedData->story_points,
                    'cardsByList'      => $cachedData->cards_by_list,
                    'memberPoints'     => $cachedData->member_points,
                    'boardDetails'     => $cachedData->board_details,
                    'backlogData'      => $cachedData->backlog_data,
                    'timestamp'        => now()->toDateTimeString(),
                    'requestedBoardId' => $boardId,
                    'cached'           => true,
                    'lastFetched'      => $cachedData->getLastFetchedFormatted(),
                ])->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                    ->header('Pragma', 'no-cache')
                    ->header('Expires', '0');
            }

            // If we're here, we need to fetch fresh data from Trello API
            \Log::info('Fetching fresh data for board ID: ' . $boardId);

            // Important: Log the actual board ID being used
            \Log::info('Fetching data for board ID: ' . $boardId, [
                'user'     => auth()->user()->name,
                'user_id'  => auth()->id(),
                'is_admin' => auth()->user()->isAdmin() ? 'Yes' : 'No',
            ]);

            // More direct approach with simpler structure and better error handling
            $cards        = [];
            $members      = [];
            $lists        = [];
            $boardDetails = null;

            // Step 0: Fetch board details first to get the name
            try {
                $boardResponse = Http::withOptions(['verify' => false])->get($this->getTrelloApiBaseUrl() . "boards/{$boardId}", [
                    'key'    => $apiKey,
                    'token'  => $apiToken,
                    'fields' => 'name,url,dateLastActivity',
                ]);

                if ($boardResponse->successful()) {
                    $boardDetails = $boardResponse->json();
                }
                // Ensure the try block is properly closed
            } catch (\Exception $e) {
                \Log::warning('Error fetching board details: ' . $e->getMessage());
                // Continue even if board fetch fails
            }

            // Step 1: Fetch cards
            try {
                $response = Http::withOptions([
                    'verify'  => false,
                    'timeout' => 30,
                ])->get($this->getTrelloApiBaseUrl() . "boards/{$boardId}/cards", [
                    'key'        => $apiKey,
                    'token'      => $apiToken,
                    'fields'     => 'name,idList,labels,desc,idMembers',
                    'pluginData' => 'true',
                    'members'    => 'true',
                ]);

                if ($response->successful()) {
                    $cards = $response->json();
                } else {
                    throw new \Exception('Failed to fetch cards. Status: ' . $response->status() . ', Error: ' . substr($response->body(), 0, 200));
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching cards: ' . $e->getMessage());
                throw new \Exception('Error fetching Trello cards: ' . $e->getMessage());
            }

            // Step 2: Fetch members
            try {
                $membersResponse = Http::withOptions(['verify' => false])->get($this->getTrelloApiBaseUrl() . "boards/{$boardId}/members", [
                    'key'    => $apiKey,
                    'token'  => $apiToken,
                    'fields' => 'id,fullName,username,avatarUrl',
                ]);

                if ($membersResponse->successful()) {
                    $members = $membersResponse->json();
                }
            } catch (\Exception $e) {
                \Log::warning('Error fetching members: ' . $e->getMessage());
                // Continue even if members fetch fails
            }

            // Step 3: Fetch lists
            try {
                $listsResponse = Http::withOptions(['verify' => false])->get($this->getTrelloApiBaseUrl() . "boards/{$boardId}/lists", [
                    'key'    => $apiKey,
                    'token'  => $apiToken,
                    'fields' => 'name,id',
                ]);

                if ($listsResponse->successful()) {
                    foreach ($listsResponse->json() as $list) {
                        $lists[$list['id']] = $list['name'];
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Error fetching lists: ' . $e->getMessage());
                // Continue even if lists fetch fails
            }

            // Generate the reports with simplified structure
            $storyPoints  = $this->calculateStoryPointsWithPlugin($cards);
            $cardsByList  = $this->organizeCardsByListWithPlugin($cards, $lists);
            $memberPoints = $this->calculateMemberStoryPoints($cards, $members, $boardId, $apiKey, $apiToken); // Add boardId and credentials here

            // Get backlog data - always fetch fresh data
            $backlogController = app()->make(BacklogController::class);
            $backlogData = $backlogController->getBacklogData();

            // Ensure backlogData is properly formatted for collections
            if ($backlogData && isset($backlogData['allBugs']) && is_array($backlogData['allBugs'])) {
                $backlogData['allBugs'] = collect($backlogData['allBugs']);
            }

            if ($backlogData && isset($backlogData['bugsByTeam']) && is_array($backlogData['bugsByTeam'])) {
                foreach ($backlogData['bugsByTeam'] as $team => $bugs) {
                    if (is_array($bugs)) {
                        $backlogData['bugsByTeam'][$team] = collect($bugs);
                    }
                }
            }

            if ($backlogData && isset($backlogData['bugsBySprint']) && is_array($backlogData['bugsBySprint'])) {
                foreach ($backlogData['bugsBySprint'] as $sprint => $bugs) {
                    if (is_array($bugs)) {
                        $backlogData['bugsBySprint'][$sprint] = collect($bugs);
                    }
                }
            }

            // Save the data to the database for future use, but exclude backlog data
            \App\Models\TrelloBoardData::updateOrCreate(
                ['board_id' => $boardId],
                [
                    'board_name'      => $boardDetails['name'] ?? 'Unknown Board',
                    'story_points'    => $storyPoints,
                    'cards_by_list'   => $cardsByList,
                    'member_points'   => $memberPoints,
                    'board_details'   => $boardDetails,
                    'last_fetched_at' => now(),
                ]
            );

            // Return the data with board details and a timestamp to prevent caching
            return response()->json([
                'storyPoints'      => $storyPoints,
                'cardsByList'      => $cardsByList,
                'memberPoints'     => $memberPoints,
                'boardDetails'     => $boardDetails,
                'backlogData'      => $backlogData,
                'timestamp'        => now()->toDateTimeString(),
                'requestedBoardId' => $boardId,
                'cached'           => false,
                'lastFetched'      => 'just now',
            ])->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (\Exception $e) {
            \Log::error('Error in fetchStoryPoints', [
                'board_id' => $request->input('board_id'),
                'message'  => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error'   => $e->getMessage(),
                'details' => app()->environment('local') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    private function getStoryPoints($pluginData)
    {
        $agileToolsPluginId = '59d4ef8cfea15a55b0086614';

        foreach ($pluginData as $data) {
            if ($data['idPlugin'] === $agileToolsPluginId) {
                $value = json_decode($data['value'], true);
                return $value['points'] ?? 0;
            }
        }

        // Fallback: try to extract from card name if plugin data doesn't contain points
        return 0;
    }

    private function calculateStoryPointsWithPlugin($cards)
    {
        $total      = 0;
        $completed  = 0;
        $inProgress = 0;
        $todo       = 0;

        foreach ($cards as $card) {
            // Extract story points from plugin data
            $points = 0;

            if (isset($card['pluginData']) && ! empty($card['pluginData'])) {
                $points = $this->getStoryPoints($card['pluginData']);
            }

            // Fallback to extracting from card name if plugin data doesn't have points
            if ($points == 0 && isset($card['name']) && preg_match('/\((\d+(\.\d+)?)\)/', $card['name'], $matches)) {
                $points = (float) $matches[1];
            }

            if ($points > 0) {
                $total += $points;

                $isDone       = false;
                $isInProgress = false;

                // Check if card has a "Done" label
                if (isset($card['labels'])) {
                    foreach ($card['labels'] as $label) {
                        if (strtolower($label['name']) === 'done') {
                            $isDone = true;
                            break;
                        } else if (strtolower($label['name']) === 'in progress') {
                            $isInProgress = true;
                            break;
                        }
                    }
                }

                if ($isDone) {
                    $completed += $points;
                } else if ($isInProgress) {
                    $inProgress += $points;
                } else {
                    $todo += $points;
                }
            }
        }

        return [
            'total'           => $total,
            'completed'       => $completed,
            'inProgress'      => $inProgress,
            'todo'            => $todo,
            'percentComplete' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
        ];
    }

    private function organizeCardsByListWithPlugin($cards, $lists)
    {
        $cardsByList = [];

        // Get members to include in response
        $memberMap = [];
        foreach ($cards as $card) {
            if (isset($card['members']) && ! empty($card['members'])) {
                foreach ($card['members'] as $member) {
                    $memberMap[$member['id']] = [
                        'id'        => $member['id'],
                        'fullName'  => $member['fullName'] ?? null,
                        'username'  => $member['username'] ?? null,
                        'avatarUrl' => $member['avatarUrl'] ?? null,
                    ];
                }
            }
        }

        foreach ($cards as $card) {
            $listId   = $card['idList'];
            $listName = isset($lists[$listId]) ? $lists[$listId] : 'Unknown List';

            if (! isset($cardsByList[$listName])) {
                $cardsByList[$listName] = [
                    'cards'       => [],
                    'totalPoints' => 0,
                ];
            }

            // Extract story points from plugin data
            $points = 0;

            if (isset($card['pluginData']) && ! empty($card['pluginData'])) {
                $points = $this->getStoryPoints($card['pluginData']);
            }

            // Fallback to extracting from card name if plugin data doesn't have points
            if ($points == 0 && isset($card['name']) && preg_match('/\((\d+(\.\d+)?)\)/', $card['name'], $matches)) {
                $points = (float) $matches[1];
            }

            // Extract member information
            $members = [];
            if (isset($card['idMembers']) && ! empty($card['idMembers'])) {
                foreach ($card['idMembers'] as $memberId) {
                    if (isset($memberMap[$memberId])) {
                        $members[] = $memberMap[$memberId];
                    }
                }
            }

            $cardsByList[$listName]['totalPoints'] += $points;
            $cardsByList[$listName]['cards'][] = [
                'id'          => $card['id'],
                'desc'        => $card['desc'],
                'name'        => $card['name'],
                'labels'      => $card['labels'],
                'idList'      => $card['idList'],
                'listName'    => $lists[$card['idList']] ?? 'Unknown List',
                'points'      => $points,
                'hasPoints'   => $points > 0,
                'members'     => $members,
                'membersFull' => collect($memberMap)->whereIn('id', $card['idMembers'])->values(),
                'url'         => "https://trello.com/c/{$card['id']}",
            ];
        }

        return $cardsByList;
    }

    /**
     * Calculate story points for each team member
     *
     * @param array $cards Cards data from Trello API
     * @param array $members Members data from Trello API
     * @param string $boardId Board ID
     * @param string $apiKey Trello API key
     * @param string $apiToken Trello API token
     * @return array
     */
    private function calculateMemberStoryPoints($cards, $members, $boardId, $apiKey, $apiToken)
    {
        $doneListIds   = [];
        $cancelListIds = [];

        // Create a map of list IDs to list names for checking card position
        try {
            $listsResponse = Http::withOptions(['verify' => false])->get($this->getTrelloApiBaseUrl() . "boards/{$boardId}/lists", [
                'key'    => $apiKey,
                'token'  => $apiToken,
                'fields' => 'name,id',
            ]);

            if ($listsResponse->successful()) {
                foreach ($listsResponse->json() as $list) {
                    $listName = strtolower($list['name']);
                    // Check for Done section lists
                    if (strpos($listName, 'done') !== false || strpos($listName, 'complete') !== false ||
                        strpos($listName, 'finished') !== false || strpos($listName, 'pass') !== false) {
                        $doneListIds[] = $list['id'];
                    }
                    // Check for Cancel section lists
                    if (strpos($listName, 'cancel') !== false || strpos($listName, 'cancelled') !== false ||
                        strpos($listName, 'canceled') !== false) {
                        $cancelListIds[] = $list['id'];
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::warning('Error fetching lists for determining done/cancel status: ' . $e->getMessage());
            // Continue even if lists fetch fails - will fall back to using labels
        }

        // Create a member map for easier lookup
        $memberMap = [];
        foreach ($members as $member) {
            // Skip cluster74 user (board admin)
            if (strtolower($member['username']) === 'cluster74') {
                continue;
            }

            $memberMap[$member['id']] = [
                'id'            => $member['id'],
                'fullName'      => $member['fullName'],
                'username'      => $member['username'],
                'avatarUrl'     => $member['avatarUrl'] ?? null,
                'pointPersonal' => 0,
                'passPoint'     => 0,
                'bugPoint'      => 0,
                'cancelPoint'   => 0,
                'finalPoint'    => 0,
                'cards'         => [],
            ];
        }

        // Analyze cards and assign points to members
        foreach ($cards as $card) {
            // Skip cards without members
            if (! isset($card['idMembers']) || empty($card['idMembers'])) {
                continue;
            }

            // Extract story points from plugin data
            $points = 0;
            if (isset($card['pluginData']) && ! empty($card['pluginData'])) {
                $points = $this->getStoryPoints($card['pluginData']);
            }

            // Fallback to extracting from card name if plugin data doesn't have points
            if ($points == 0 && isset($card['name']) && preg_match('/\((\d+(\.\d+)?)\)/', $card['name'], $matches)) {
                $points = (float) $matches[1];
            }

            // Skip cards without points
            if ($points <= 0) {
                continue;
            }

            // Determine card status based on section (list) AND labels
            $isDone      = false;
            $isCancelled = false;

            // First, check if card is in a Done list
            if (in_array($card['idList'], $doneListIds)) {
                $isDone = true;
            }
            // Then check if card is in a Cancel list
            else if (in_array($card['idList'], $cancelListIds)) {
                $isCancelled = true;
            }

            // If not determined by list, check labels (as backup)
            if (! $isDone && ! $isCancelled && isset($card['labels'])) {
                foreach ($card['labels'] as $label) {
                    $labelName = strtolower($label['name']);

                    if ($labelName === 'done' || $labelName === 'pass') {
                        $isDone = true;
                        break;
                    } else if ($labelName === 'cancel' || $labelName === 'cancelled') {
                        $isCancelled = true;
                        break;
                    }
                }
            }

            // Assign points to each member on the card
            foreach ($card['idMembers'] as $memberId) {
                if (isset($memberMap[$memberId])) {
                    $memberMap[$memberId]['pointPersonal'] += $points;

                    if ($isDone) {
                        $memberMap[$memberId]['passPoint'] += $points;
                    } else if ($isCancelled) {
                        $memberMap[$memberId]['cancelPoint'] += $points;
                    } else {
                        // Bug points are for cards that are neither Done nor Cancelled
                        $memberMap[$memberId]['bugPoint'] += $points;
                    }

                    $cardStatus = $isDone ? 'pass' : ($isCancelled ? 'cancel' : 'bug');

                    $memberMap[$memberId]['cards'][] = [
                        'name'   => $card['name'],
                        'points' => $points,
                        'status' => $cardStatus,
                    ];
                }
            }
        }

        // Calculate final points and pass percentage
        $result = array_values($memberMap);
        foreach ($result as &$member) {
            // Final = pass (as specified)
            $member['finalPoint'] = $member['passPoint'];

            // Pass percentage formula: (passPoint / pointPersonal) * 100
            $member['passPercentage'] = $member['pointPersonal'] > 0
            ? round(($member['passPoint'] / $member['pointPersonal']) * 100, 2)
            : 0;
        }

        // Sort by total points (descending)
        usort($result, function ($a, $b) {
            return $b['pointPersonal'] <=> $a['pointPersonal'];
        });

        return $result;
    }

    public function storyPointsReport()
    {
        // Get current user's boards
        $apiKey = $this->getSetting('trello_api_key');
        $apiToken = $this->getSetting('trello_api_token');
        $boards = [];
        $defaultBoardId = null;
        $error = null;
        $singleBoard = false;
        $boardData = null;
        $backlogData = null;
        $userBoards = []; // Initialize userBoards array
        $user = auth()->user();

        try {
            if (!$apiKey || !$apiToken) {
                throw new \Exception('Trello API credentials not configured.');
            }

            // Try to get boards for the user
            $boards = $this->fetchBoards($apiKey, $apiToken);

            // Filter boards based on user role
            if (!$user->isAdmin()) {
                // For testers and developers, filter boards they are members of
                try {
                    $userBoards = $this->filterBoardsForUser($boards, $apiKey, $apiToken);
                    // Replace the main boards array with filtered boards for non-admins
                    $boards = $userBoards;

                    \Log::info('Filtered boards for non-admin user', [
                        'user' => $user->name,
                        'role' => $user->role,
                        'board_count' => count($boards),
                        'board_names' => collect($boards)->pluck('name')->toArray()
                    ]);

                    if (empty($boards)) {
                        throw new \Exception('You do not have access to any boards. Please contact your administrator if you believe this is an error.');
                    }
                } catch (\Exception $e) {
                    \Log::error('Error filtering boards for user', [
                        'user' => $user->name,
                        'role' => $user->role,
                        'error' => $e->getMessage()
                    ]);
                    $boards = [];
                    $userBoards = [];
                    $error = $e->getMessage();
                }
            } else {
                $userBoards = $boards; // Admins can see all boards
                \Log::info('Admin user accessing all boards', [
                    'user' => $user->name,
                    'board_count' => count($boards)
                ]);
            }

            // Handle cases based on the number of available boards
            if (count($boards) === 0 && !$error) {
                $error = "No Trello boards available. Please ensure you have access to at least one board.";
            } elseif (count($boards) === 1) {
                $singleBoard = true;
                $defaultBoardId = $boards[0]['id'];
            } else {
                // Multiple boards - set default or use the user's preference
                $defaultBoardId = session('trello.default_board_id');

                // If no default board is set in session, or if the default board is not in the user's allowed boards
                if (!$defaultBoardId || !collect($boards)->pluck('id')->contains($defaultBoardId)) {
                    $defaultBoardId = $boards[0]['id'];
                }
            }

            // Check if we have cached data for the default board
            if ($defaultBoardId) {
                // Verify user has access to this board
                if (!collect($boards)->pluck('id')->contains($defaultBoardId)) {
                    throw new \Exception('You do not have access to this board.');
                }

                // Get board members to verify access
                $boardMembers = $this->fetchBoardMembers($defaultBoardId, $apiKey, $apiToken);
                $userHasAccess = false;

                foreach ($boardMembers as $member) {
                    if ($member['fullName'] === $user->name) {
                        $userHasAccess = true;
                        break;
                    }
                }

                if (!$user->isAdmin() && !$userHasAccess) {
                    throw new \Exception('You do not have access to view this board\'s report.');
                }

                $cachedData = TrelloBoardData::where('board_id', $defaultBoardId)->first();

                if ($cachedData) {
                    $boardData = [
                        'storyPoints' => $cachedData->story_points,
                        'cardsByList' => $cachedData->cards_by_list,
                        'memberPoints' => $cachedData->member_points,
                        'boardDetails' => $cachedData->board_details,
                        'lastFetched' => $cachedData->getLastFetchedFormatted(),
                    ];
                }
            }

            // Always fetch fresh backlog data
            if ($defaultBoardId && ($user->isAdmin() || $userHasAccess)) {
                $backlogController = app()->make(BacklogController::class);
                $backlogData = $backlogController->getBacklogData();
            }

            // Get sprint info
            $sprintInfo = $this->getCurrentSprintInfo();

        } catch (\Exception $e) {
            $error = $e->getMessage();
            \Log::error('Error in storyPointsReport', [
                'user' => $user->name,
                'role' => $user->role,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        // Get current date
        $currentDate = Carbon::now()->format('F d, Y');

        return view('trello.story-points-report', [
            'boards' => $boards,
            'defaultBoardId' => $defaultBoardId,
            'singleBoard' => $singleBoard,
            'error' => $error,
            'currentDate' => $currentDate,
            'boardData' => $boardData,
            'backlogData' => $backlogData,
            'currentSprintNumber' => $sprintInfo['currentSprintNumber'] ?? null,
            'sprintDateRange' => $sprintInfo['sprintDateRange'] ?? null,
            'sprintDurationDisplay' => $sprintInfo['sprintDurationDisplay'] ?? null,
            'currentSprintDay' => $sprintInfo['currentSprintDay'] ?? null,
            'sprintTotalDays' => $sprintInfo['sprintTotalDays'] ?? null,
            'daysRemaining' => $sprintInfo['daysRemaining'] ?? null,
            'nextReportDate' => $sprintInfo['nextReportDate'] ?? null,
            'currentWeekNumber' => $sprintInfo['currentWeekNumber'] ?? null,
            'currentSprintStartDate' => $sprintInfo['currentSprintStartDate'] ?? null,
            'currentSprintEndDate' => $sprintInfo['currentSprintEndDate'] ?? null,
            'sprintProgressPercent' => $sprintInfo['sprintProgressPercent'] ?? 0,
        ]);
    }

    /**
     * Get current sprint information for display
     *
     * @return array Sprint information for the view
     */
    private function getCurrentSprintInfo()
    {
        try {
            \Log::debug('Starting getCurrentSprintInfo in TrelloController');

            // Get sprint settings controller to use consistent calculations
            $sprintSettingsController = new \App\Http\Controllers\SprintSettingsController();

                                                                              // Get current sprint settings with explicit defaults
            $sprintDuration = (int) $this->getSetting('sprint_duration', 7);  // Default to 7 days
            $sprintStartDay = (int) $this->getSetting('sprint_start_day', 1); // Monday default

            \Log::debug('Retrieved sprint settings', [
                'sprintDuration' => $sprintDuration,
                'sprintStartDay' => $sprintStartDay,
            ]);

            // Get current sprint info directly from SprintSettingsController's index method
            $now               = Carbon::now();
            $currentWeekNumber = $now->weekOfYear;

            try {
                $currentSprintNumber = $sprintSettingsController->getCurrentSprintNumber();
                \Log::debug('Retrieved current sprint number', ['currentSprintNumber' => $currentSprintNumber]);
            } catch (\Exception $e) {
                \Log::error('Error getting sprint number', [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);
                $currentSprintNumber = max(1, $now->weekOfYear);
            }

            // Calculate next report date (not needed on report page, but included for consistency)
            $sprintEndTime = $this->getSetting('sprint_end_time', '18:00');

            try {
                $nextReportDate = $sprintSettingsController->getNextReportDate($sprintStartDay, $sprintDuration, $sprintEndTime);
                \Log::debug('Retrieved next report date', ['nextReportDate' => $nextReportDate]);
            } catch (\Exception $e) {
                \Log::error('Error calculating next report date', [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);
                $nextReportDate = 'Not available';
            }

            // Calculate current sprint start date based on settings
            try {
                $sprintStartDate             = $sprintSettingsController->getCurrentSprintStartDate($sprintStartDay, $sprintDuration);
                $currentSprintStartDate      = $sprintStartDate->format('M j, Y');
                $currentSprintStartDateShort = $sprintStartDate->format('M j');
                \Log::debug('Retrieved sprint start date', ['sprintStartDate' => $sprintStartDate->format('Y-m-d')]);
            } catch (\Exception $e) {
                \Log::error('Error calculating sprint start date', [
                    'message' => $e->getMessage(),
                    'trace'   => $e->getTraceAsString(),
                ]);
                $sprintStartDate             = $now->copy()->startOfWeek();
                $currentSprintStartDate      = $sprintStartDate->format('M j, Y');
                $currentSprintStartDateShort = $sprintStartDate->format('M j');
            }

            // Calculate sprint end date (duration - 1 days after start)
            $sprintEndDate             = $sprintStartDate->copy()->addDays($sprintDuration - 1);
            $currentSprintEndDate      = $sprintEndDate->format('M j, Y');
            $currentSprintEndDateShort = $sprintEndDate->format('M j');
            $sprintDateRange           = $currentSprintStartDateShort . ' - ' . $currentSprintEndDateShort;

            // Verify sprint end date is not before start date (safety check)
            if ($sprintEndDate->lt($sprintStartDate)) {
                \Log::error('Sprint end date is before start date', [
                    'start'    => $sprintStartDate->format('Y-m-d'),
                    'end'      => $sprintEndDate->format('Y-m-d'),
                    'duration' => $sprintDuration,
                ]);

                // Fix the dates - ensure end date is always after start date
                $sprintEndDate             = $sprintStartDate->copy()->addDays(max(1, $sprintDuration - 1));
                $currentSprintEndDate      = $sprintEndDate->format('M j, Y');
                $currentSprintEndDateShort = $sprintEndDate->format('M j');
                $sprintDateRange           = $currentSprintStartDateShort . ' - ' . $currentSprintEndDateShort;
            }

            \Log::debug('Calculated sprint end date', ['sprintEndDate' => $sprintEndDate->format('Y-m-d')]);

                                                  // Calculate progress percentage
            $totalDays = max(1, $sprintDuration); // Ensure no division by zero

            // Calculate days elapsed since sprint start
            $daysElapsed = 0;
            if ($now->startOfDay()->gte($sprintStartDate->startOfDay())) {
                                                                                                   // If today is after or equal to sprint start date
                $daysElapsed = $sprintStartDate->startOfDay()->diffInDays($now->startOfDay()) + 1; // +1 to include current day
            }

            // Ensure days elapsed doesn't exceed the total sprint duration
            $daysElapsed = min($daysElapsed, $totalDays);

            // Calculate sprint progress percentage - ensure no division by zero
            $sprintProgressPercent = min(100, round(($daysElapsed / $totalDays) * 100, 1));

            // Calculate days remaining in the sprint
            if ($now->startOfDay()->gt($sprintEndDate->startOfDay())) {
                // If we're past the end date
                $daysRemaining = 0;
            } else {
                // Calculate days remaining
                $daysRemaining = $now->startOfDay()->diffInDays($sprintEndDate->startOfDay());
                // If today is the last day, set to 0
                if ($now->startOfDay()->eq($sprintEndDate->startOfDay())) {
                    $daysRemaining = 0;
                }
            }

            // Sprint duration display
            $weeksCount            = $sprintDuration / 7;
            $sprintDurationDisplay = $weeksCount == 1 ? '1 Week' : $weeksCount . ' Weeks';

            \Log::debug('Sprint info for report page', [
                'startDate'     => $sprintStartDate->format('Y-m-d'),
                'endDate'       => $sprintEndDate->format('Y-m-d'),
                'duration'      => $sprintDuration,
                'daysElapsed'   => $daysElapsed,
                'daysRemaining' => $daysRemaining,
                'progress'      => $sprintProgressPercent,
            ]);

            return [
                'currentWeekNumber'           => $currentWeekNumber,
                'currentSprintNumber'         => $currentSprintNumber,
                'nextReportDate'              => $nextReportDate,
                'currentSprintStartDate'      => $currentSprintStartDate,
                'currentSprintEndDate'        => $currentSprintEndDate,
                'sprintProgressPercent'       => $sprintProgressPercent,
                'daysElapsed'                 => $daysElapsed,
                'daysRemaining'               => $daysRemaining,
                'sprintDateRange'             => $sprintDateRange,
                'sprintDurationDisplay'       => $sprintDurationDisplay,
                'currentSprintDay'            => $daysElapsed,
                'sprintTotalDays'             => $totalDays,
                'currentSprintStartDateShort' => $currentSprintStartDateShort,
                'currentSprintEndDateShort'   => $currentSprintEndDateShort,
            ];
        } catch (\Exception $e) {
            \Log::error('Error calculating sprint info in TrelloController::getCurrentSprintInfo', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => $e->getTraceAsString(),
            ]);

            // Get the default values directly from Carbon to avoid potential errors
            $now       = Carbon::now();
            $startDate = $now->copy()->startOfWeek();
            $endDate   = $now->copy()->endOfWeek();

            return [
                'currentWeekNumber'           => $now->weekOfYear,
                'currentSprintNumber'         => 0, // Using 0 to indicate an error occurred
                'nextReportDate'              => 'Not available',
                'currentSprintStartDate'      => $startDate->format('M j, Y'),
                'currentSprintEndDate'        => $endDate->format('M j, Y'),
                'sprintProgressPercent'       => 0,
                'daysElapsed'                 => 0,
                'daysRemaining'               => 7,
                'sprintDateRange'             => $startDate->format('M j') . ' - ' . $endDate->format('M j'),
                'sprintDurationDisplay'       => '1 Week',
                'currentSprintDay'            => 0,
                'sprintTotalDays'             => 7,
                'currentSprintStartDateShort' => $startDate->format('M j'),
                'currentSprintEndDateShort'   => $endDate->format('M j'),
            ];
        }
    }

    /**
     * Fetch members of a specific board
     *
     * @param string $boardId The board ID
     * @param string $apiKey Trello API key
     * @param string $apiToken Trello API token
     * @return array Board members
     */
    private function fetchBoardMembers($boardId, $apiKey, $apiToken)
    {
        try {
            $response = Http::withOptions(['verify' => false])
                ->get($this->getTrelloApiBaseUrl() . "boards/{$boardId}/members", [
                    'key'    => $apiKey,
                    'token'  => $apiToken,
                    'fields' => 'id,fullName,username',
                ]);

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Exception $e) {
            \Log::error('Error fetching board members', [
                'boardId' => $boardId,
                'error'   => $e->getMessage(),
            ]);
            return [];
        }
    }

    private function fetchBoards($apiKey, $apiToken)
    {
        try {
            $response = Http::withOptions(['verify' => false])->get($this->getTrelloApiBaseUrl() . 'members/me/boards', [
                'key'    => $apiKey,
                'token'  => $apiToken,
                'fields' => 'name,url,idOrganization',
                'filter' => 'open',
            ]);

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Exception $e) {
            \Log::error('Error fetching boards: ' . $e->getMessage());
        }

        return [];
    }

    /**
     * Fetch bug cards from the board
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchBugCards(Request $request)
    {
        try {
            $boardId = $request->input('board_id');

            if (! $boardId) {
                return response()->json(['error' => 'Board ID is required'], 400);
            }

            // Get Trello API credentials from settings
            $credentials = $this->getTrelloCredentials();
            $apiKey      = $credentials['apiKey'];
            $apiToken    = $credentials['apiToken'];

            // Fetch board details for verification
            $boardDetails = [];
            try {
                $boardResponse = Http::withOptions(['verify' => false])->get($this->getTrelloApiBaseUrl() . "boards/{$boardId}", [
                    'key'   => $apiKey,
                    'token' => $apiToken,
                ]);

                if ($boardResponse->successful()) {
                    $boardDetails = $boardResponse->json();
                }
            } catch (\Exception $e) {
                \Log::warning('Error fetching board details: ' . $e->getMessage());
            }

            // Step 1: Fetch all cards from the board
            $cards = [];
            $lists = [];

            try {
                $cardsResponse = Http::withOptions(['verify' => false])->get($this->getTrelloApiBaseUrl() . "boards/{$boardId}/cards", [
                    'key'           => $apiKey,
                    'token'         => $apiToken,
                    'fields'        => 'id,name,desc,idList,idMembers,labels,pluginData',
                    'members'       => 'true',
                    'member_fields' => 'id,fullName,username,avatarUrl',
                    'pluginData'    => 'true',
                ]);

                if ($cardsResponse->successful()) {
                    $cards = $cardsResponse->json();
                } else {
                    return response()->json(['error' => 'Failed to fetch cards from Trello'], 500);
                }
            } catch (\Exception $e) {
                \Log::error('Error fetching cards: ' . $e->getMessage());
                return response()->json(['error' => 'Failed to fetch cards: ' . $e->getMessage()], 500);
            }

            // Step 2: Fetch all lists from the board
            try {
                $listsResponse = Http::withOptions(['verify' => false])->get($this->getTrelloApiBaseUrl() . "boards/{$boardId}/lists", [
                    'key'    => $apiKey,
                    'token'  => $apiToken,
                    'fields' => 'name,id',
                ]);

                if ($listsResponse->successful()) {
                    foreach ($listsResponse->json() as $list) {
                        $lists[$list['id']] = $list['name'];
                    }
                }
            } catch (\Exception $e) {
                \Log::warning('Error fetching lists: ' . $e->getMessage());
            }

            // Organize cards by list, but only include bug cards with story points
            $listsData = [];

            // First, identify Done and Cancel lists by name
            $doneListNames   = [];
            $cancelListNames = [];

            foreach ($lists as $listId => $listName) {
                $lowerListName = strtolower($listName);
                if (
                    strpos($lowerListName, 'done') !== false ||
                    strpos($lowerListName, 'complete') !== false ||
                    strpos($lowerListName, 'finished') !== false ||
                    strpos($lowerListName, 'pass') !== false
                ) {
                    $doneListNames[] = $listName;
                } elseif (
                    strpos($lowerListName, 'cancel') !== false ||
                    strpos($lowerListName, 'cancelled') !== false ||
                    strpos($lowerListName, 'canceled') !== false
                ) {
                    $cancelListNames[] = $listName;
                }

                // Initialize the lists data structure
                $listsData[$listName] = [
                    'cards' => [],
                ];
            }

            // Now process each card
            foreach ($cards as $card) {
                $listId   = $card['idList'];
                $listName = $lists[$listId] ?? 'Unknown List';

                // Skip if list is unknown
                if (! isset($listsData[$listName])) {
                    continue;
                }

                // Extract story points
                $points = 0;
                if (isset($card['pluginData']) && ! empty($card['pluginData'])) {
                    $points = $this->getStoryPoints($card['pluginData']);
                }

                // Fallback to extracting from card name
                if ($points == 0 && isset($card['name']) && preg_match('/\((\d+(\.\d+)?)\)/', $card['name'], $matches)) {
                    $points = (float) $matches[1];
                }

                // Add the card with its detail
                $cardData = [
                    'id'          => $card['id'],
                    'name'        => $card['name'],
                    'description' => $card['desc'] ?? '',
                    'points'      => $points,
                    'labels'      => $card['labels'] ?? [],
                    'members'     => $card['members'] ?? [],
                    'idList'      => $card['idList'],
                    'idMembers'   => $card['idMembers'] ?? [],
                    'url'         => "https://trello.com/c/{$card['id']}",
                ];

                $listsData[$listName]['cards'][] = $cardData;
            }

            return response()->json([
                'listsData'        => $listsData,
                'boardDetails'     => $boardDetails,
                'timestamp'        => now()->toDateTimeString(),
                'requestedBoardId' => $boardId,
            ])->header('Cache-Control', 'no-cache, no-store, must-revalidate')
                ->header('Pragma', 'no-cache')
                ->header('Expires', '0');
        } catch (\Exception $e) {
            \Log::error('Error in fetchBugCards', [
                'board_id' => $request->input('board_id'),
                'message'  => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);
            return response()->json([
                'error'   => $e->getMessage(),
                'details' => app()->environment('local') ? $e->getTraceAsString() : null,
            ], 500);
        }
    }

    /**
     * Save the board selection to session for later use in the report creation form
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function saveBoardSelection(Request $request)
    {
        $request->validate([
            'board_id'          => 'required|string',
            'board_name'        => 'required|string',
            'story_points_data' => 'nullable|string',
            'bug_cards_data'    => 'nullable|string',
        ]);

        // Store in session
        session(['selected_board' => $request->board_id]);
        session(['selected_board_name' => $request->board_name]);

        // Store report data
        if ($request->has('story_points_data')) {
            // Ensure data is valid JSON before storing
            $storyPointsData = $request->story_points_data;
            try {
                // Check if it's valid JSON by decoding and re-encoding it
                $decoded = json_decode($storyPointsData, true);
                if (json_last_error() === JSON_ERROR_NONE && $decoded) {
                    // Re-encode to ensure it's properly formatted
                    $storyPointsData = json_encode($decoded);
                }
            } catch (\Exception $e) {
                \Log::warning('Invalid story_points_data format: ' . $e->getMessage());
            }
            session(['current_story_points_data' => $storyPointsData]);
        }

        if ($request->has('bug_cards_data')) {
            // Ensure data is valid JSON before storing
            $bugCardsData = $request->bug_cards_data;
            try {
                // Check if it's valid JSON by decoding and re-encoding it
                $decoded = json_decode($bugCardsData, true);
                if (json_last_error() === JSON_ERROR_NONE && $decoded) {
                    // Re-encode to ensure it's properly formatted
                    $bugCardsData = json_encode($decoded);
                }
            } catch (\Exception $e) {
                \Log::warning('Invalid bug_cards_data format: ' . $e->getMessage());
            }
            session(['current_bug_cards_data' => $bugCardsData]);
        }

        \Log::info('Board selection saved', [
            'board_id'                 => $request->board_id,
            'board_name'               => $request->board_name,
            'has_story_points_data'    => $request->has('story_points_data'),
            'has_bug_cards_data'       => $request->has('bug_cards_data'),
            'story_points_data_length' => $request->has('story_points_data') ? strlen($request->story_points_data) : 0,
            'bug_cards_data_length'    => $request->has('bug_cards_data') ? strlen($request->bug_cards_data) : 0,
        ]);

        return response()->json(['success' => true]);
    }

    /**
     * Get the current sprint number based on auto-increment from previous reports this year
     */
    private function getAutoIncrementedSprintNumber()
    {
        $now         = Carbon::now();
        $currentYear = $now->year;

        // Find the last sprint number from this year's reports
        $lastReport = \App\Models\SavedReport::where('report_name', 'like', "Sprint %")
            ->where('created_at', '>=', Carbon::createFromDate($currentYear, 1, 1))
            ->where('created_at', '<=', Carbon::createFromDate($currentYear, 12, 31))
            ->orderBy('id', 'desc')
            ->first();

        if ($lastReport) {
            // Extract sprint number from report name
            $matches = [];
            if (preg_match('/Sprint (\d+)/', $lastReport->report_name, $matches)) {
                $lastSprintNumber = (int) $matches[1];
                // Increment by 1
                return $lastSprintNumber + 1;
            }
        }

        // If no previous reports found for this year, or couldn't parse sprint number,
        // Calculate based on week number as fallback
        $weekNumber     = $now->weekOfYear;
        $sprintDuration = (int) $this->getSetting('sprint_duration', 7);

        // For weekly sprints, sprint number = week number
        // For longer sprints, calculate sprint number based on duration
        $sprintNumber = $sprintDuration > 0
        ? ceil($weekNumber / ($sprintDuration / 7))
        : 1;

        // If this is first sprint of the year, return 1
        return max(1, min(52, $sprintNumber));
    }

    /**
     * Get sprint information as JSON
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSprintInfo()
    {
        try {
            $sprintData = $this->getCurrentSprintInfo();
            return response()->json($sprintData);
        } catch (\Exception $e) {
            \Log::error('Error in getSprintInfo: ' . $e->getMessage());
            return response()->json([
                'error'   => 'Failed to get sprint information',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fetch Trello data for the story points report
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchTrelloData(Request $request)
    {
        return $this->fetchStoryPoints($request);
    }

    /**
     * Filter boards for the current user based on membership
     *
     * @param array $boards Board data from Trello API
     * @param string $apiKey Trello API key
     * @param string $apiToken Trello API token
     * @return array Filtered boards where user is a member
     */
    private function filterBoardsForUser($boards, $apiKey, $apiToken)
    {
        $userName   = auth()->user()->name;
        $userBoards = [];

        foreach ($boards as $board) {
            // Fetch board members
            $members = $this->fetchBoardMembers($board['id'], $apiKey, $apiToken);

            // Check if user is a member of this board
            foreach ($members as $member) {
                if ($member['fullName'] === $userName) {
                    $userBoards[] = $board;
                    break;
                }
            }
        }

        return $userBoards;
    }

    // Add a method to handle client-side logging
    public function logMessage(Request $request)
    {
        $message = $request->input('message', 'No message provided');
        $data = $request->input('data', []);
        
        \Log::info('Client log: ' . $message, $data);
        
        return response()->json(['success' => true]);
    }
}
