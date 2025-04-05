<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SavedReport;
use App\Models\User;

class TestSavedReportCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:saved-report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test creating and reading saved reports with JSON data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing SavedReport creation with JSON data...');
        
        // 1. Get an admin user
        $user = User::where('role', 'admin')->first();
        if (!$user) {
            $this->error('No admin user found!');
            return 1;
        }
        
        $this->info("Using user: {$user->name} (ID: {$user->id})");
        
        // 2. Create test data
        $storyPointsData = json_encode([
            'summary' => [
                'planPoints' => 20,
                'actualPoints' => 15,
                'remainPercent' => '25%',
                'percentComplete' => '75%',
                'currentSprintPoints' => 10,
                'actualCurrentSprint' => 7,
                'boardName' => 'Test Board',
                'lastUpdated' => 'Last updated: ' . now()->format('Y-m-d H:i:s')
            ],
            'teamMembers' => [
                [
                    'name' => 'Test User 1',
                    'pointPersonal' => 10,
                    'pass' => 8,
                    'bug' => 2,
                    'cancel' => 0,
                    'final' => 8,
                    'passPercent' => '80%'
                ],
                [
                    'name' => 'Test User 2',
                    'pointPersonal' => 10,
                    'pass' => 7,
                    'bug' => 1,
                    'cancel' => 2,
                    'final' => 7,
                    'passPercent' => '70%'
                ]
            ],
            'totals' => [
                'totalPersonal' => 20,
                'totalPass' => 15,
                'totalBug' => 3,
                'totalCancel' => 2,
                'totalFinal' => 15
            ]
        ]);
        
        $bugCardsData = json_encode([
            'bugCards' => [
                [
                    'name' => 'Test Bug 1',
                    'points' => 2,
                    'list' => 'To Do',
                    'description' => 'This is a test bug description',
                    'members' => 'Test User 1',
                    'priorityClass' => 'priority-high'
                ],
                [
                    'name' => 'Test Bug 2',
                    'points' => 1,
                    'list' => 'In Progress',
                    'description' => 'Another test bug description',
                    'members' => 'Test User 2',
                    'priorityClass' => 'priority-medium'
                ]
            ],
            'bugCount' => '2 bugs',
            'totalBugPoints' => 3
        ]);
        
        // 3. Create the test report
        $this->info('Creating test report...');
        
        try {
            $report = new SavedReport();
            $report->user_id = $user->id;
            $report->report_name = 'Test Report ' . now()->format('Y-m-d H:i:s');
            $report->board_id = 'test-board-id';
            $report->board_name = 'Test Board Name';
            $report->notes = 'This is a test report created by the TestSavedReportCommand';
            $report->story_points_data = $storyPointsData;
            $report->bug_cards_data = $bugCardsData;
            $report->save();
            
            $this->info("Report created with ID: {$report->id}");
            
            // 4. Retrieve and verify the saved report
            $savedReport = SavedReport::find($report->id);
            $this->info('Retrieved report has:');
            $this->info('- story_points_data: ' . (empty($savedReport->story_points_data) ? 'Empty' : 'Present (' . strlen(json_encode($savedReport->story_points_data)) . ' bytes)'));
            $this->info('- bug_cards_data: ' . (empty($savedReport->bug_cards_data) ? 'Empty' : 'Present (' . strlen(json_encode($savedReport->bug_cards_data)) . ' bytes)'));
            
            // 5. Check if JSON is correctly casted
            $this->info('Testing JSON casting:');
            $this->info('- story_points_data is ' . (is_array($savedReport->story_points_data) ? 'array' : gettype($savedReport->story_points_data)));
            $this->info('- bug_cards_data is ' . (is_array($savedReport->bug_cards_data) ? 'array' : gettype($savedReport->bug_cards_data)));
            
            // Test accessors
            $this->info('Testing accessors:');
            $this->info('- storyPointsStructured: ' . (empty($savedReport->storyPointsStructured) ? 'Empty' : 'Present (' . count($savedReport->storyPointsStructured['teamMembers'] ?? []) . ' team members)'));
            $this->info('- bugCardsStructured: ' . (empty($savedReport->bugCardsStructured) ? 'Empty' : 'Present (' . count($savedReport->bugCardsStructured['bugCards'] ?? []) . ' bug cards)'));
            
            return 0;
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
            $this->error('Trace: ' . $e->getTraceAsString());
            return 1;
        }
    }
}
