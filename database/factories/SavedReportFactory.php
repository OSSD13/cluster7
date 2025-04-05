<?php

namespace Database\Factories;

use App\Models\SavedReport;
use App\Models\Sprint;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class SavedReportFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = SavedReport::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $teamNames = ['Team Alpha', 'Team Beta', 'Team Charlie'];
        $teamName = $this->faker->randomElement($teamNames);
        
        $sprint = Sprint::inRandomOrder()->first() ?? Sprint::factory()->create();
        $user = User::inRandomOrder()->first() ?? User::factory()->create();
        
        $reportData = $this->generateReportData($teamName);
        
        // Generate a datetime within the sprint period
        $reportDate = Carbon::create(
            $sprint->start_date->year,
            $sprint->start_date->month,
            $sprint->start_date->day
        )->addDays($this->faker->numberBetween(0, $sprint->duration - 1));
        
        return [
            'user_id' => $user->id,
            'sprint_id' => $sprint->id,
            'name' => "Sprint {$sprint->sprint_number}: {$teamName} Report",
            'report_data' => $reportData,
            'created_at' => $reportDate,
            'updated_at' => $reportDate,
        ];
    }
    
    /**
     * Generate structured report data
     * 
     * @param string $teamName
     * @return array
     */
    public function generateReportData(string $teamName): array
    {
        // Define team names
        $teamNames = ['Team Alpha', 'Team Beta', 'Team Charlie'];
        
        // Predefined team members for each team
        $teamMembersMap = [
            'Team Alpha' => ['Alex Garcia', 'Ashley Wilson', 'Aaron Peterson', 'Amanda Lewis'],
            'Team Beta' => ['Benjamin Chen', 'Brianna Taylor', 'Brandon Kim'],
            'Team Charlie' => ['Carlos Rodriguez', 'Chloe Davis', 'Cameron Mitchell'],
        ];
        
        $teamMembers = $teamMembersMap[$teamName] ?? array_merge(...array_values($teamMembersMap));
        
        // Story points data
        $teamMembersData = [];
        $totalPersonal = 0;
        $totalPass = 0;
        $totalBug = 0;
        $totalCancel = 0;
        $totalFinal = 0;
        
        // Generate data for each team member
        foreach ($teamMembers as $member) {
            $pointPersonal = $this->faker->numberBetween(5, 30);
            $pass = $this->faker->numberBetween(0, $pointPersonal);
            $cancel = $this->faker->numberBetween(0, $pointPersonal - $pass);
            $bug = $pointPersonal - $pass - $cancel;
            $final = $pass;
            
            $passPercent = $pointPersonal > 0 ? round(($pass / $pointPersonal) * 100) . '%' : '0%';
            
            $totalPersonal += $pointPersonal;
            $totalPass += $pass;
            $totalBug += $bug;
            $totalCancel += $cancel;
            $totalFinal += $final;
            
            $teamMembersData[] = [
                'name' => $member,
                'pointPersonal' => $pointPersonal,
                'pass' => $pass,
                'bug' => $bug,
                'cancel' => $cancel,
                'final' => $final,
                'passPercent' => $passPercent,
            ];
        }
        
        // Bug cards data
        $bugCards = [];
        $bugCount = $this->faker->numberBetween(1, 8);
        $totalBugPoints = 0;
        
        // Generate bug cards for the team
        for ($i = 0; $i < $bugCount; $i++) {
            $priority = $this->getRandomPriority();
            $points = $this->faker->randomElement([1, 2, 3, 5, 8]);
            $totalBugPoints += $points;
            
            // Randomly assign bugs to team members
            $assignee = $this->faker->randomElement($teamMembers);
            
            $bugCards[] = [
                'id' => 'BUG-' . $this->faker->numberBetween(100, 999),
                'name' => 'Bug: ' . $this->getRandomBugTitle(),
                'url' => 'https://trello.com/c/' . substr(md5(uniqid()), 0, 8),
                'points' => $points,
                'assigned' => $assignee,
                'labels' => ['Bug', $priority],
                'status' => $this->faker->randomElement(['active', 'completed', 'active', 'active']), // 75% active, 25% completed
            ];
        }
        
        // Calculate overall report percentages
        $percentComplete = $totalPersonal > 0 ? round(($totalPass / $totalPersonal) * 100) : 0;
        $remainPercent = 100 - $percentComplete;
        
        // Create a backlog with some bugs from other teams
        $backlogBugs = [];
        $backlogBugCount = $this->faker->numberBetween(0, 5);
        $backlogTotalPoints = 0;
        
        if ($backlogBugCount > 0) {
            $otherTeams = array_diff($teamNames, [$teamName]);
            
            for ($i = 0; $i < $backlogBugCount; $i++) {
                $originTeam = $this->faker->randomElement($otherTeams);
                $priority = $this->getRandomPriority();
                $points = $this->faker->randomElement([1, 2, 3, 5, 8]);
                $backlogTotalPoints += $points;
                
                $originTeamMembers = $teamMembersMap[$originTeam] ?? [];
                $assignee = !empty($originTeamMembers) ? $this->faker->randomElement($originTeamMembers) : null;
                
                $backlogBugs[] = [
                    'id' => 'BUG-' . $this->faker->numberBetween(1000, 1999),
                    'name' => 'Backlog: ' . $this->getRandomBugTitle(),
                    'url' => 'https://trello.com/c/' . substr(md5(uniqid()), 0, 8),
                    'points' => $points,
                    'assigned' => $assignee,
                    'labels' => ['Bug', $priority, 'Backlog'],
                    'team' => $originTeam,
                    'sprint_origin' => $this->faker->numberBetween(1, 4),
                    'status' => 'active',
                ];
            }
        }
        
        // Complete structured report data
        return [
            'summary' => [
                'boardName' => $teamName,
                'planPoints' => $totalPersonal,
                'actualPoints' => $totalPass,
                'remainPercent' => $remainPercent . '%',
                'percentComplete' => $percentComplete . '%',
                'currentSprintPoints' => $totalPersonal,
                'actualCurrentSprint' => $totalPass,
                'lastUpdated' => 'Last updated: ' . Carbon::now()->format('M j, Y g:i A'),
            ],
            'teamMembers' => $teamMembersData,
            'totals' => [
                'totalPersonal' => $totalPersonal,
                'totalPass' => $totalPass,
                'totalBug' => $totalBug,
                'totalCancel' => $totalCancel,
                'totalFinal' => $totalFinal,
            ],
            'bug_cards' => [
                $teamName => $bugCards
            ],
            'bugCount' => $bugCount . ' ' . ($bugCount === 1 ? 'bug' : 'bugs'),
            'totalBugPoints' => $totalBugPoints,
            'backlog' => [
                $teamName => $backlogBugs
            ],
            'backlogBugCount' => $backlogBugCount . ' ' . ($backlogBugCount === 1 ? 'bug' : 'bugs'),
            'backlogTotalPoints' => $backlogTotalPoints,
        ];
    }
    
    /**
     * Get a random bug title
     */
    public function getRandomBugTitle()
    {
        $titles = [
            'UI rendering issue in the dashboard',
            'Authentication fails for special characters',
            'Data not saved when network connection is lost',
            'Incorrect calculation in points summary',
            'Mobile view breaks on small screens',
            'Notifications not showing for certain users',
            'Export feature creates incomplete CSV files',
            'Memory leak in chart components',
            'Filter not applied correctly for date ranges',
            'Search function returns incorrect results',
            'Performance issue with large datasets',
            'API timeout when syncing with Trello',
            'Session expires prematurely',
            'Color contrast issues for accessibility',
            'Form validation does not show error messages'
        ];
        
        return $titles[array_rand($titles)];
    }
    
    /**
     * Get a random priority level
     */
    public function getRandomPriority()
    {
        $priorities = ['High', 'Medium', 'Low'];
        return $priorities[array_rand($priorities)];
    }
    
    /**
     * Configure the factory to create reports with backlog bugs
     *
     * @param int $count Number of backlog bugs to include
     * @return $this
     */
    public function withBacklog(int $count = 5)
    {
        return $this->state(function (array $attributes) use ($count) {
            $reportData = $attributes['report_data'];
            
            // Add more backlog bugs if requested
            if (is_array($reportData)) {
                $teamName = $reportData['summary']['boardName'] ?? 'Unknown Team';
                
                $backlogBugs = [];
                $backlogTotalPoints = 0;
                
                for ($i = 0; $i < $count; $i++) {
                    $priority = $this->getRandomPriority();
                    $points = $this->faker->randomElement([1, 2, 3, 5, 8]);
                    $backlogTotalPoints += $points;
                    
                    $backlogBugs[] = [
                        'id' => 'BUG-' . (2000 + $i),
                        'name' => 'Backlog: ' . $this->getRandomBugTitle(),
                        'url' => 'https://trello.com/c/' . substr(md5(uniqid()), 0, 8),
                        'points' => $points,
                        'assigned' => 'Backlog User',
                        'labels' => ['Bug', $priority, 'Backlog'],
                        'team' => $teamName,
                        'sprint_origin' => $this->faker->numberBetween(1, 4),
                        'status' => 'active',
                    ];
                }
                
                // Update backlog data
                $reportData['backlog'][$teamName] = $backlogBugs;
                $reportData['backlogBugCount'] = $count . ' ' . ($count === 1 ? 'bug' : 'bugs');
                $reportData['backlogTotalPoints'] = $backlogTotalPoints;
            }
            
            return ['report_data' => $reportData];
        });
    }
    
    /**
     * Configure the factory to create reports for a specific team
     *
     * @param string $teamName Team name to use
     * @return $this
     */
    public function forTeam(string $teamName)
    {
        return $this->state(function (array $attributes) use ($teamName) {
            $sprintId = $attributes['sprint_id'] ?? null;
            $sprintNumber = null;
            
            if ($sprintId) {
                try {
                    $sprint = Sprint::where('id', $sprintId)->first();
                    if ($sprint) {
                        $sprintNumber = $sprint->sprint_number;
                    }
                } catch (\Exception $e) {
                    // Sprint not found or error accessing it
                }
            }
            
            // If no sprint found, try to get current sprint
            if (!$sprintNumber) {
                $currentSprint = Sprint::getCurrentSprint();
                if ($currentSprint) {
                    $sprintNumber = $currentSprint->sprint_number;
                    // Update the sprint_id to match for consistency
                    if (!isset($attributes['sprint_id'])) {
                        $attributes['sprint_id'] = $currentSprint->id;
                    }
                } else {
                    // Last resort - use a default number
                    $sprintNumber = 1;
                }
            }
            
            $reportData = $this->generateReportData($teamName);
            
            return [
                'name' => "Sprint {$sprintNumber}: {$teamName} Report",
                'report_data' => $reportData,
                'sprint_id' => $attributes['sprint_id'] ?? null,
            ];
        });
    }
} 