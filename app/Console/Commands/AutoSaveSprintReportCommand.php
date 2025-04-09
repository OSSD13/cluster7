<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SavedReport;
use App\Models\User;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;
use App\Services\TrelloService;

class AutoSaveSprintReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:auto-save-sprint';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically save sprint reports at the end of each sprint';

    /**
     * The Trello service instance.
     *
     * @var \App\Services\TrelloService
     */
    protected $trelloService;

    /**
     * Create a new command instance.
     *
     * @param \App\Services\TrelloService $trelloService
     * @return void
     */
    public function __construct(TrelloService $trelloService)
    {
        parent::__construct();
        $this->trelloService = $trelloService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting automatic sprint report saving...');
        
        // Get the current sprint number
        $sprintNumber = $this->getCurrentSprintNumber();
        $this->info("Current Sprint: {$sprintNumber}");
        
        // Get Trello credentials
        $credentials = $this->getTrelloCredentials();
        if (!$credentials['apiKey'] || !$credentials['apiToken']) {
            $this->error('Trello API credentials are not configured.');
            return 1;
        }
        
        // Get all boards
        $boards = $this->fetchBoards($credentials['apiKey'], $credentials['apiToken']);
        if (empty($boards)) {
            $this->error('No boards found or could not connect to Trello API.');
            return 1;
        }
        
        $this->info('Found ' . count($boards) . ' Trello boards.');
        
        // Find admin user to associate the reports with
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $this->error('No admin user found to associate reports with.');
            return 1;
        }
        
        $savedCount = 0;
        foreach ($boards as $board) {
            $this->info("Processing board: {$board['name']} ({$board['id']})");
            
            try {
                // Fetch report data for the board
                list($storyPointsData, $bugCardsData) = $this->fetchBoardData($board['id'], $credentials);
                
                if (!$storyPointsData || !$bugCardsData) {
                    $this->warn("Skipping board {$board['name']} - could not fetch data.");
                    continue;
                }
                
                // Prepare report name
                $reportName = "Sprint {$sprintNumber} - {$board['name']} - " . Carbon::now()->format('Y-m-d');
                
                // Create the report
                $report = new SavedReport();
                $report->user_id = $admin->id;
                $report->report_name = $reportName;
                $report->board_id = $board['id'];
                $report->board_name = $board['name'];
                $report->notes = "Automatically generated sprint report for Sprint {$sprintNumber}";
                $report->story_points_data = $storyPointsData;
                $report->bug_cards_data = $bugCardsData;
                $report->save();
                
                $this->info("Saved report: {$reportName}");
                $savedCount++;
            } catch (\Exception $e) {
                $this->error("Error processing board {$board['name']}: {$e->getMessage()}");
                // Continue with next board
            }
        }
        
        $this->info("Auto-save complete. Saved {$savedCount} sprint reports.");
        return 0;
    }
    
    /**
     * Get the current sprint number based on auto-increment from previous reports this year
     */
    private function getCurrentSprintNumber()
    {
        $now = Carbon::now();
        $currentYear = $now->year;
        
        // Find the last sprint number from this year's reports
        $lastReport = SavedReport::where('report_name', 'like', "Sprint %")
            ->where('created_at', '>=', Carbon::createFromDate($currentYear, 1, 1))
            ->where('created_at', '<=', Carbon::createFromDate($currentYear, 12, 31))
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastReport) {
            // Extract sprint number from report name
            $matches = [];
            if (preg_match('/Sprint (\d+)/', $lastReport->report_name, $matches)) {
                $lastSprintNumber = (int)$matches[1];
                // Increment by 1
                return $lastSprintNumber + 1;
            }
        }
        
        // If no previous reports found for this year, or couldn't parse sprint number,
        // Calculate based on week number as fallback
        $weekNumber = $now->weekOfYear;
        $sprintDuration = (int)$this->getSetting('sprint_duration', 7);
        
        // For weekly sprints, sprint number = week number
        // For longer sprints, calculate sprint number based on duration
        $sprintNumber = $sprintDuration > 0 
            ? ceil($weekNumber / ($sprintDuration / 7))
            : 1;
        
        // If this is first sprint of the year, return 1
        return max(1, min(52, $sprintNumber));
    }
    
    /**
     * Get Trello credentials from settings
     */
    private function getTrelloCredentials()
    {
        return [
            'apiKey' => $this->getSetting('trello_api_key'),
            'apiToken' => $this->getSetting('trello_api_token')
        ];
    }
    
    /**
     * Get setting value
     */
    private function getSetting($key, $default = null)
    {
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
    
    /**
     * Fetch all boards from Trello
     */
    private function fetchBoards($apiKey, $apiToken)
    {
        try {
            $response = Http::withOptions(['verify' => false])
                ->get($this->trelloService->getBaseUrl() . 'members/me/boards', [
                    'key' => $apiKey,
                    'token' => $apiToken,
                    'fields' => 'name,url,id'
                ]);
                
            if ($response->failed()) {
                $this->error('Failed to fetch boards from Trello API');
                return [];
            }
            
            return $response->json();
        } catch (\Exception $e) {
            $this->error('Exception fetching boards: ' . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Fetch report data for a specific board
     * Returns [storyPointsData, bugCardsData]
     */
    private function fetchBoardData($boardId, $credentials)
    {
        $apiKey = $credentials['apiKey'];
        $apiToken = $credentials['apiToken'];
        $baseUrl = $this->trelloService->getBaseUrl();
        
        try {
            // Fetch story points data
            $storyPointsResponse = Http::withOptions(['verify' => false])
                ->get($baseUrl . "boards/{$boardId}/cards", [
                    'key' => $apiKey,
                    'token' => $apiToken,
                    'fields' => 'name,idList,labels,desc,idMembers',
                    'pluginData' => 'true',
                    'members' => 'true'
                ]);
                
            if ($storyPointsResponse->failed()) {
                $this->error("Failed to fetch story points data for board {$boardId}");
                return [null, null];
            }
            
            $cards = $storyPointsResponse->json();
            
            // Fetch lists
            $listsResponse = Http::withOptions(['verify' => false])
                ->get($baseUrl . "boards/{$boardId}/lists", [
                    'key' => $apiKey,
                    'token' => $apiToken,
                    'fields' => 'name,id'
                ]);
                
            if ($listsResponse->failed()) {
                $this->error("Failed to fetch lists for board {$boardId}");
                return [null, null];
            }
            
            $lists = [];
            foreach ($listsResponse->json() as $list) {
                $lists[$list['id']] = $list['name'];
            }
            
            // Fetch members
            $membersResponse = Http::withOptions(['verify' => false])
                ->get($baseUrl . "boards/{$boardId}/members", [
                    'key' => $apiKey,
                    'token' => $apiToken,
                    'fields' => 'id,fullName,username,avatarUrl'
                ]);
                
            if ($membersResponse->failed()) {
                $this->error("Failed to fetch members for board {$boardId}");
                return [null, null];
            }
            
            $members = $membersResponse->json();
            
            // Fetch board details
            $boardResponse = Http::withOptions(['verify' => false])
                ->get($baseUrl . "boards/{$boardId}", [
                    'key' => $apiKey,
                    'token' => $apiToken,
                    'fields' => 'name,url,dateLastActivity'
                ]);
                
            $boardDetails = $boardResponse->json();
            
            // Process the data
            $storyPoints = $this->calculateStoryPoints($cards);
            $memberPoints = $this->calculateMemberStoryPoints($cards, $members, $boardId, $lists);
            
            // Format into report data
            $storyPointsData = json_encode([
                'summary' => [
                    'planPoints' => $storyPoints['total'],
                    'actualPoints' => $storyPoints['completed'],
                    'remainPercent' => round(($storyPoints['total'] - $storyPoints['completed']) / max(1, $storyPoints['total']) * 100) . '%',
                    'percentComplete' => $storyPoints['percentComplete'] . '%',
                    'currentSprintPoints' => $storyPoints['total'],
                    'actualCurrentSprint' => $storyPoints['completed'],
                    'boardName' => $boardDetails['name'] ?? 'Unknown Board',
                    'lastUpdated' => 'Last updated: ' . Carbon::now()->format('Y-m-d H:i:s')
                ],
                'teamMembers' => array_map(function($member) {
                    return [
                        'name' => $member['fullName'],
                        'pointPersonal' => $member['pointPersonal'],
                        'pass' => $member['passPoint'],
                        'bug' => $member['bugPoint'],
                        'cancel' => $member['cancelPoint'],
                        'final' => $member['finalPoint'],
                        'passPercent' => $member['passPercentage'] . '%'
                    ];
                }, $memberPoints),
                'totals' => [
                    'totalPersonal' => array_sum(array_column($memberPoints, 'pointPersonal')),
                    'totalPass' => array_sum(array_column($memberPoints, 'passPoint')),
                    'totalBug' => array_sum(array_column($memberPoints, 'bugPoint')),
                    'totalCancel' => array_sum(array_column($memberPoints, 'cancelPoint')),
                    'totalFinal' => array_sum(array_column($memberPoints, 'finalPoint'))
                ]
            ]);
            
            // Process bug cards
            $bugCards = $this->extractBugCards($cards, $lists);
            
            $bugCardsData = json_encode([
                'bugCards' => $bugCards,
                'bugCount' => count($bugCards) . ' bugs',
                'totalBugPoints' => array_sum(array_column($bugCards, 'points'))
            ]);
            
            return [$storyPointsData, $bugCardsData];
        } catch (\Exception $e) {
            $this->error("Exception fetching data for board {$boardId}: " . $e->getMessage());
            return [null, null];
        }
    }
    
    /**
     * Extract bug cards from the cards data
     */
    private function extractBugCards($cards, $lists)
    {
        $bugCards = [];
        
        foreach ($cards as $card) {
            // Check if this is a bug card (has bug label)
            $isBugCard = false;
            $priority = 'priority-none';
            
            if (isset($card['labels'])) {
                foreach ($card['labels'] as $label) {
                    $labelName = strtolower($label['name']);
                    
                    if (strpos($labelName, 'bug') !== false) {
                        $isBugCard = true;
                    }
                    
                    // Check for priority
                    if (strpos($labelName, 'priority') !== false || strpos($labelName, 'high') !== false) {
                        $priority = 'priority-high';
                    } elseif (strpos($labelName, 'medium') !== false) {
                        $priority = 'priority-medium';
                    } elseif (strpos($labelName, 'low') !== false) {
                        $priority = 'priority-low';
                    }
                }
            }
            
            if ($isBugCard) {
                // Extract points
                $points = 0;
                if (isset($card['pluginData']) && !empty($card['pluginData'])) {
                    $points = $this->getStoryPoints($card['pluginData']);
                }
                
                // Member names
                $memberNames = [];
                if (isset($card['members'])) {
                    foreach ($card['members'] as $member) {
                        $memberNames[] = $member['fullName'];
                    }
                }
                
                // Get list name
                $listName = $lists[$card['idList']] ?? 'Unknown List';
                
                $bugCards[] = [
                    'name' => $card['name'],
                    'points' => $points,
                    'list' => $listName,
                    'description' => $card['desc'] ?? '',
                    'members' => implode(', ', $memberNames),
                    'priorityClass' => $priority
                ];
            }
        }
        
        return $bugCards;
    }
    
    /**
     * Get story points from plugin data
     */
    private function getStoryPoints($pluginData)
    {
        $agileToolsPluginId = '59d4ef8cfea15a55b0086614';
        
        foreach ($pluginData as $data) {
            if ($data['idPlugin'] === $agileToolsPluginId) {
                $value = json_decode($data['value'], true);
                return $value['points'] ?? 0;
            }
        }
        
        return 0;
    }
    
    /**
     * Calculate story points from cards
     */
    private function calculateStoryPoints($cards)
    {
        $total = 0;
        $completed = 0;
        $inProgress = 0;
        $todo = 0;
        
        foreach ($cards as $card) {
            // Extract story points
            $points = 0;
            
            if (isset($card['pluginData']) && !empty($card['pluginData'])) {
                $points = $this->getStoryPoints($card['pluginData']);
            }
            
            // Fallback to extracting from card name if plugin data doesn't have points
            if ($points == 0 && isset($card['name']) && preg_match('/\((\d+(\.\d+)?)\)/', $card['name'], $matches)) {
                $points = (float) $matches[1];
            }
            
            if ($points > 0) {
                $total += $points;
                
                $isDone = false;
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
            'total' => $total,
            'completed' => $completed,
            'inProgress' => $inProgress,
            'todo' => $todo,
            'percentComplete' => $total > 0 ? round(($completed / $total) * 100, 2) : 0
        ];
    }
    
    /**
     * Calculate member story points
     */
    private function calculateMemberStoryPoints($cards, $members, $boardId, $lists)
    {
        $doneListIds = [];
        $cancelListIds = [];
        
        // Identify Done and Cancel lists
        foreach ($lists as $listId => $listName) {
            $listName = strtolower($listName);
            // Check for Done section lists
            if (strpos($listName, 'done') !== false || strpos($listName, 'complete') !== false ||
                strpos($listName, 'finished') !== false || strpos($listName, 'pass') !== false) {
                $doneListIds[] = $listId;
            }
            // Check for Cancel section lists
            if (strpos($listName, 'cancel') !== false || strpos($listName, 'cancelled') !== false ||
               strpos($listName, 'canceled') !== false) {
                $cancelListIds[] = $listId;
            }
        }
        
        // Create a member map
        $memberMap = [];
        foreach ($members as $member) {
            if (strtolower($member['username'] ?? '') === 'cluster74') {
                continue;
            }
            
            $memberMap[$member['id']] = [
                'id' => $member['id'],
                'fullName' => $member['fullName'] ?? $member['username'] ?? 'Unknown Member',
                'username' => $member['username'] ?? '',
                'avatarUrl' => $member['avatarUrl'] ?? null,
                'pointPersonal' => 0,
                'passPoint' => 0,
                'bugPoint' => 0,
                'cancelPoint' => 0,
                'finalPoint' => 0,
                'cards' => []
            ];
        }
        
        // Analyze cards
        foreach ($cards as $card) {
            if (!isset($card['idMembers']) || empty($card['idMembers'])) {
                continue;
            }
            
            // Extract points
            $points = 0;
            if (isset($card['pluginData']) && !empty($card['pluginData'])) {
                $points = $this->getStoryPoints($card['pluginData']);
            }
            
            if ($points == 0 && isset($card['name']) && preg_match('/\((\d+(\.\d+)?)\)/', $card['name'], $matches)) {
                $points = (float) $matches[1];
            }
            
            if ($points <= 0) {
                continue;
            }
            
            // Determine card status
            $isDone = false;
            $isCancelled = false;
            
            // Check by list
            if (in_array($card['idList'], $doneListIds)) {
                $isDone = true;
            } else if (in_array($card['idList'], $cancelListIds)) {
                $isCancelled = true;
            }
            
            // Check by labels if not determined by list
            if (!$isDone && !$isCancelled && isset($card['labels'])) {
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
            
            // Assign points
            foreach ($card['idMembers'] as $memberId) {
                if (isset($memberMap[$memberId])) {
                    $memberMap[$memberId]['pointPersonal'] += $points;
                    
                    if ($isDone) {
                        $memberMap[$memberId]['passPoint'] += $points;
                    } else if ($isCancelled) {
                        $memberMap[$memberId]['cancelPoint'] += $points;
                    } else {
                        $memberMap[$memberId]['bugPoint'] += $points;
                    }
                    
                    $cardStatus = $isDone ? 'pass' : ($isCancelled ? 'cancel' : 'bug');
                    
                    $memberMap[$memberId]['cards'][] = [
                        'name' => $card['name'],
                        'points' => $points,
                        'status' => $cardStatus
                    ];
                }
            }
        }
        
        // Calculate final points and percentages
        $result = array_values($memberMap);
        foreach ($result as &$member) {
            // Final = pass
            $member['finalPoint'] = $member['passPoint'];
            
            // Pass percentage
            $member['passPercentage'] = $member['pointPersonal'] > 0
                ? round(($member['passPoint'] / $member['pointPersonal']) * 100, 2)
                : 0;
        }
        
        // Sort by total points
        usort($result, function($a, $b) {
            return $b['pointPersonal'] <=> $a['pointPersonal'];
        });
        
        return $result;
    }
} 