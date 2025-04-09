<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Sprint;
use App\Models\SprintReport;
use App\Models\User;
use Illuminate\Support\Carbon;
use App\Models\SavedReport;

class CustomSprintSeeder extends Seeder
{
    /**
     * The predefined team members to use in reports
     */
    protected $teamMembers = [
        'XBananAX',
        'Narathip Saentaweesuk',
        'Patyot Sompran',
        'Pawarit Sinchoom',
        'Natchaya Chokchaichumnankij',
        'Niyada Butcahn',
        'Sutaphat Thahin',
        'Thanakorn Prasertdeengam'
    ];

    /**
     * The predefined teams to create reports for
     */
    protected $teams = [
        'Team Alpha',
        'Team Beta'
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding sprints with custom data...');

        // Start date (January 1st of the current year)
        $startDate = Carbon::create(now()->year, 1, 1);

        // Create 5 sprints, each 7 days long
        $this->command->info("Creating 5 sprints (1 week each) from Jan 1");

        $sprints = [];

        for ($i = 0; $i < 5; $i++) {
            $sprintStartDate = $startDate->copy()->addWeeks($i);
            $sprintEndDate = $sprintStartDate->copy()->addDays(6); // 7 days duration (1 week)

            // All sprints before current date are marked as completed
            $status = $sprintEndDate->lt(now()) ? 'completed' : 'planned';
            $progressPercentage = $status === 'completed' ? 100 : 0;
            $daysElapsed = $status === 'completed' ? 7 : 0;
            $daysRemaining = $status === 'completed' ? 0 : 7;

            // Create the sprint
            $sprint = Sprint::create([
                'sprint_number' => $i + 1,
                'start_date' => $sprintStartDate,
                'end_date' => $sprintEndDate,
                'duration' => 7, // 1 week
                'status' => $status,
                'progress_percentage' => $progressPercentage,
                'days_elapsed' => $daysElapsed,
                'days_remaining' => $daysRemaining,
                'notes' => "Sprint " . ($i + 1) . " (Week of " . $sprintStartDate->format('M j') . ")",
                'created_at' => $sprintStartDate->copy()->subDays(1),
                'updated_at' => $sprintEndDate,
            ]);

            $sprints[] = $sprint;
        }

        $this->command->info('Created ' . count($sprints) . ' completed sprints, each 1 week long');

        // Make sure we have at least one user to associate with reports
        $user = User::first() ?: User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->command->info('Seeding sprint reports for 2 teams with specific team members...');

        // Maintain a backlog of bugs from previous sprints
        $backlogBugs = [];

        // Create 2 reports for each team, for each sprint
        foreach ($sprints as $index => $sprint) {
            $isLastSprint = $index === count($sprints) - 1;
            $isFirstSprint = $index === 0;

            // Generate new bugs for this sprint (for last sprint only)
            $newSprintBugs = $isLastSprint ? $this->generateBugCardsData(true) : [];

            // Track bug cards for backlog
            if (!$isFirstSprint) {
                // For each team, we'll add bugs to the backlog
                foreach ($this->teams as $teamName) {
                    // Add specific number of new bugs for each sprint after the first one
                    $backlogBugs[$teamName] = $backlogBugs[$teamName] ?? [];

                    // Update the backlog for this sprint by resolving some bugs and adding new ones
                    if ($index < count($sprints) - 1) {
                        $backlogBugs[$teamName] = $this->updateBacklog($backlogBugs[$teamName], $sprint->sprint_number);
                    }
                }
            } else {
                // Initialize backlog for first sprint
                foreach ($this->teams as $teamName) {
                    $backlogBugs[$teamName] = [];
                }
            }

            // For each team, create reports
            foreach ($this->teams as $index => $teamName) {
                // Create seed data for this team's report
                $reportData = [
                    'sprint_name' => "Sprint {$sprint->sprint_number}",
                    'team_name' => $teamName,
                    'members' => $this->teamMembers,
                    'bug_cards' => [],
                    'backlog' => []
                ];

                // Add team-specific bug cards for this sprint
                if ($isLastSprint && isset($newSprintBugs[$teamName])) {
                    $reportData['bug_cards'][$teamName] = $newSprintBugs[$teamName];
                } else {
                    // For older sprints, generate some random bugs
                    $randomBugs = $this->generateRandomBugCards(2, 5);

                    // Mark some of these bugs as completed for older sprints
                    foreach ($randomBugs as $key => $bug) {
                        // 70% chance to mark as completed for older sprints
                        if (rand(1, 10) <= 7) {
                            $randomBugs[$key]['status'] = 'completed';
                        }
                    }

                    $reportData['bug_cards'][$teamName] = $randomBugs;
                }

                // Add backlog bugs for this team if they exist
                if (!empty($backlogBugs[$teamName])) {
                    $reportData['backlog'][$teamName] = $backlogBugs[$teamName];
                }

                // Create 2 reports for each team for each sprint
                for ($i = 0; $i < 2; $i++) {
                    $savedReport = new SavedReport([
                        'user_id' => 1, // Admin user
                        'sprint_id' => $sprint->id,
                        'name' => $teamName . ' Report v' . ($i + 1),
                        'report_data' => json_encode($reportData),
                    ]);
                    $savedReport->save();

                    $this->command->info("Created report for {$teamName} (Sprint {$sprint->sprint_number}): {$savedReport->name}");
                }
            }
        }

        $reportCount = SavedReport::count();
        $this->command->info("Created {$reportCount} sprint reports (2 reports per team)");
        $this->command->info("Added backlog data to reports after Sprint 1");
    }

    /**
     * Generate story points data with the specified team members
     */
    private function generateStoryPointsData($teamName)
    {
        // Shuffle and pick 3-5 team members for this report
        $shuffledMembers = collect($this->teamMembers)->shuffle();
        $selectedMembers = $shuffledMembers->take(rand(3, 5))->values()->all();

        $teamMembersData = [];
        $totalAssigned = 0;
        $totalCompleted = 0;

        // Generate data for each team member
        foreach ($selectedMembers as $member) {
            $assignedPoints = rand(5, 30);
            $completedPoints = rand(0, $assignedPoints);

            $totalAssigned += $assignedPoints;
            $totalCompleted += $completedPoints;

            $teamMembersData[] = [
                'name' => $member,
                'assignedPoints' => $assignedPoints,
                'completedPoints' => $completedPoints,
                'percentComplete' => $completedPoints > 0 ? round(($completedPoints / $assignedPoints) * 100) : 0,
                'avatarUrl' => "https://ui-avatars.com/api/?name=" . urlencode($member) . "&size=32",
            ];
        }

        // Calculate percentage complete
        $percentComplete = $totalAssigned > 0 ? round(($totalCompleted / $totalAssigned) * 100) : 0;

        return [
            'teamMembers' => $teamMembersData,
            'summary' => [
                'boardName' => $teamName,
                'totalPoints' => $totalAssigned,
                'completedPoints' => $totalCompleted,
                'percentComplete' => $percentComplete,
            ],
            'totals' => [
                'totalAssigned' => $totalAssigned,
                'totalCompleted' => $totalCompleted,
                'percentComplete' => $percentComplete,
            ],
        ];
    }

    /**
     * Generate bug cards data
     */
    private function generateBugCardsData($isLastSprint = false)
    {
        $bugCards = [];
        $bugCount = rand(0, 8);
        $totalBugPoints = 0;

        for ($i = 0; $i < $bugCount; $i++) {
            $points = rand(1, 8);
            $totalBugPoints += $points;

            // Randomly assign bugs to team members
            $assignee = rand(0, 1) ? $this->teamMembers[array_rand($this->teamMembers)] : null;

            $bugCards[] = [
                'id' => 'BUG-' . rand(100, 999),
                'name' => 'Bug: ' . $this->getRandomBugTitle(),
                'url' => 'https://trello.com/c/' . substr(md5(uniqid()), 0, 8),
                'points' => $points,
                'assigned' => $assignee,
                'labels' => ['Bug', $this->getRandomPriority()],
            ];
        }

        return [
            'bugCards' => $bugCards,
            'bugCount' => $bugCount . ' ' . ($bugCount === 1 ? 'bug' : 'bugs'),
            'totalBugPoints' => $totalBugPoints,
        ];
    }

    /**
     * Get a random bug title
     */
    private function getRandomBugTitle()
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
    private function getRandomPriority()
    {
        $priorities = ['High', 'Medium', 'Low'];
        return $priorities[array_rand($priorities)];
    }

    /**
     * Generate empty bug cards data
     */
    private function generateEmptyBugCardsData()
    {
        return [
            'bugCards' => [],
            'bugCount' => '0 bugs',
            'totalBugPoints' => 0,
        ];
    }

    /**
     * Generate backlog bugs
     */
    private function generateBacklogBugs($min = 1, $max = 3, $sprintNumber = 0)
    {
        $bugs = [];
        $bugCount = rand($min, $max);

        for ($i = 0; $i < $bugCount; $i++) {
            $points = rand(1, 5);

            // Randomly assign bugs to team members
            $assignee = rand(0, 1) ? $this->teamMembers[array_rand($this->teamMembers)] : null;

            $springSuffix = $sprintNumber > 0 ? " (from Sprint {$sprintNumber})" : "";

            // Generate a stable bug ID based on sprint and index
            $bugId = 'BLG-' . $sprintNumber . '-' . ($i + 100);

            $bugs[] = [
                'id' => $bugId,
                'name' => 'Backlog: ' . $this->getRandomBacklogBugTitle() . $springSuffix,
                'url' => 'https://trello.com/c/' . substr(md5(uniqid()), 0, 8),
                'points' => $points,
                'assigned' => $assignee,
                'labels' => ['Backlog', $this->getRandomPriority()],
                'sprint_origin' => $sprintNumber,
                'status' => 'active', // Mark as active by default
            ];
        }

        return $bugs;
    }

    /**
     * Update the backlog by resolving some bugs and adding new ones
     */
    private function updateBacklog($currentBacklog, $sprintNumber)
    {
        $updatedBacklog = [];

        // Randomly resolve some existing bugs (50% chance per bug)
        foreach ($currentBacklog as $bug) {
            // 50% chance to resolve the bug
            if (rand(0, 1) === 0) {
                // This bug is considered resolved and won't be added to the updated backlog
                continue;
            }

            // If the bug remains unresolved, add it to the updated backlog
            $updatedBacklog[] = $bug;
        }

        // Add some new bugs
        $newBugs = $this->generateBacklogBugs(1, 2, $sprintNumber);
        $updatedBacklog = array_merge($updatedBacklog, $newBugs);

        return $updatedBacklog;
    }

    /**
     * Get a random backlog bug title
     */
    private function getRandomBacklogBugTitle()
    {
        $titles = [
            'Legacy code needs refactoring',
            'Tech debt in authentication service',
            'Missing validation in forms',
            'Improve error handling',
            'Broken layout on specific browser',
            'Accessibility issue in navigation',
            'Slow database query',
            'Memory leak in old component',
            'Outdated documentation',
            'Security vulnerability in dependencies',
            'Performance issue in search feature',
            'Inconsistent styling in older UI',
            'Browser compatibility issue',
            'Internationalization bug'
        ];

        return $titles[array_rand($titles)];
    }

    /**
     * Generate random bug cards data
     */
    private function generateRandomBugCards($min = 1, $max = 5)
    {
        $bugCards = [];
        $bugCount = rand($min, $max);

        for ($i = 0; $i < $bugCount; $i++) {
            $points = rand(1, 8);

            // Randomly assign bugs to team members
            $assignee = rand(0, 1) ? $this->teamMembers[array_rand($this->teamMembers)] : null;

            $bugCards[] = [
                'id' => 'BUG-' . rand(100, 999),
                'name' => 'Bug: ' . $this->getRandomBugTitle(),
                'url' => 'https://trello.com/c/' . substr(md5(uniqid()), 0, 8),
                'points' => $points,
                'assigned' => $assignee,
                'labels' => ['Bug', $this->getRandomPriority()],
            ];
        }

        return $bugCards;
    }
}
