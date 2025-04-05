<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sprint;
use App\Models\SprintReport;
use App\Models\SavedReport;
use App\Models\User;
use Carbon\Carbon;
use Database\Factories\SavedReportFactory;

class OptimizedReportSeeder extends Seeder
{
    /**
     * Teams to create reports for.
     *
     * @var array
     */
    protected $teams = [
        'Team Alpha',
        'Team Beta',
        'Team Charlie'
    ];
    
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Creating optimized dataset with sprints, reports, and backlog data...');
        
        // Create a series of sprints with realistic dates
        $this->createSequentialSprints();
        
        // Set sprint configuration settings
        $this->configureSprintSettings();
        
        // Create sprint reports for each team for each sprint
        $this->createSprintReports();
        
        // Create saved reports with comprehensive data
        $this->createSavedReports();
        
        $this->command->info('Optimized dataset creation completed successfully!');
    }
    
    /**
     * Create sequential sprints with realistic dates.
     */
    protected function createSequentialSprints(): void
    {
        $this->command->info('Creating sequential sprints...');
        
        // Start date of January 1st of 2025
        $startDate = Carbon::create(2025, 1, 1)->startOfDay();
        
        // Set simulated current date to April 5, 2025 (for proper sprint status)
        $simulatedNow = Carbon::create(2025, 4, 5)->startOfDay();
        
        $sprintNumber = 1;
        $duration = 7; // 1 week sprints
        
        $currentDate = $startDate->copy();
        $createdSprints = 0;
        $activeSprintCreated = false;
        
        while (!$activeSprintCreated) {
            $endDate = $currentDate->copy()->addDays($duration - 1);
            
            // Determine status based on simulated current date (Apr 5, 2025)
            $status = 'completed';
            $progress = 100;
            $daysElapsed = $duration;
            $daysRemaining = 0;
            
            // If the sprint contains the simulated current date, mark as active
            if ($currentDate->lte($simulatedNow) && $endDate->gte($simulatedNow)) {
                $status = 'active';
                $daysElapsed = $currentDate->diffInDays($simulatedNow) + 1;
                $daysRemaining = $simulatedNow->diffInDays($endDate);
                $progress = min(100, round(($daysElapsed / $duration) * 100, 1));
                $activeSprintCreated = true; // We'll stop after creating the active sprint
            }
            
            // Create the sprint
            $sprint = Sprint::create([
                'sprint_number' => $sprintNumber,
                'start_date' => $currentDate->toDateTimeString(),
                'end_date' => $endDate->toDateTimeString(),
                'duration' => $duration,
                'status' => $status,
                'progress_percentage' => $progress,
                'days_elapsed' => $daysElapsed,
                'days_remaining' => $daysRemaining,
            ]);
            
            $this->command->info("Created Sprint #{$sprintNumber} ({$currentDate->format('M j, Y')} - {$endDate->format('M j, Y')}) - Status: {$status}");
            
            // Move to next sprint period
            $currentDate = $endDate->copy()->addDay();
            $sprintNumber++;
            $createdSprints++;
        }
        
        $this->command->info("Created {$createdSprints} sprints from January 1st to April 2025 (no planned sprints), with current date set to April 5, 2025");
    }
    
    /**
     * Configure sprint settings in the database.
     */
    protected function configureSprintSettings(): void
    {
        $this->command->info('Configuring sprint settings...');
        
        // Set core sprint settings
        $this->updateSetting('sprint_duration', 7);  // 7-day sprints
        $this->updateSetting('sprint_start_day', 1); // Start on Monday (1)
        $this->updateSetting('sprint_end_time', '18:00'); // End at 6 PM
        $this->updateSetting('auto_save_enabled', true);
        
        // Find the active sprint (should be Sprint #14)
        $activeSprint = Sprint::where('status', 'active')->first();
        
        if ($activeSprint) {
            // Set the active sprint as the current sprint
            $this->updateSetting('custom_sprint_number', $activeSprint->sprint_number);
            $this->command->info("Set current sprint to Sprint #{$activeSprint->sprint_number} (Active: {$activeSprint->formatted_start_date} - {$activeSprint->formatted_end_date})");
        } else {
            // Fallback to the latest sprint if no active sprint found
            $latestSprint = Sprint::orderBy('sprint_number', 'desc')->first();
            if ($latestSprint) {
                $this->updateSetting('custom_sprint_number', $latestSprint->sprint_number);
                $this->command->info("Set current sprint to Sprint #{$latestSprint->sprint_number} (Latest available)");
            }
        }
        
        $this->command->info('Sprint settings configured successfully');
    }
    
    /**
     * Update a setting value.
     *
     * @param string $key
     * @param mixed $value
     * @return void
     */
    private function updateSetting($key, $value)
    {
        \App\Models\Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
    
    /**
     * Create sprint reports for each team for each sprint.
     */
    protected function createSprintReports(): void
    {
        $this->command->info('Creating sprint reports for each team...');
        
        // Get all sprints
        $sprints = Sprint::all();
        
        foreach ($sprints as $sprint) {
            $this->command->info("Creating reports for Sprint #{$sprint->sprint_number}");
            
            // For each team, create reports
            foreach ($this->teams as $teamName) {
                // Create 2 reports per team per sprint (v1 and v2)
                SprintReport::factory()
                    ->forTeam($teamName)
                    ->count(2)
                    ->for($sprint)
                    ->create();
                
                $this->command->info("  Created 2 reports for {$teamName}");
            }
            
            // Add backlog data to reports after Sprint 1
            if ($sprint->sprint_number > 1) {
                // Add backlog to one report per team
                foreach ($this->teams as $teamName) {
                    $report = SprintReport::where('sprint_id', $sprint->id)
                        ->where('board_name', $teamName)
                        ->first();
                    
                    if ($report) {
                        $report = SprintReport::factory()
                            ->forTeam($teamName)
                            ->withBacklog(rand(3, 7))
                            ->for($sprint)
                            ->create();
                        
                        $this->command->info("  Added backlog data to a report for {$teamName}");
                    }
                }
            }
        }
        
        $this->command->info('Sprint reports creation completed');
    }
    
    /**
     * Create saved reports with comprehensive data.
     */
    protected function createSavedReports(): void
    {
        $this->command->info('Creating saved reports with comprehensive backlog data...');
        
        // Get all existing sprints
        $sprints = Sprint::all();
        
        if ($sprints->isEmpty()) {
            $this->command->warn('No sprints found - skipping saved reports creation');
            return;
        }
        
        $admin = User::where('email', 'admin@devperf.com')->first();
        
        if (!$admin) {
            $admin = User::first();
            if (!$admin) {
                $admin = User::factory()->create([
                    'name' => 'Admin User',
                    'email' => 'admin@devperf.com',
                    'role' => 'admin'
                ]);
            }
        }
        
        foreach ($sprints as $sprint) {
            // Create saved reports for each team
            foreach ($this->teams as $teamName) {
                // Create a saved report directly
                $savedReportFactory = new \Database\Factories\SavedReportFactory();
                $reportData = $savedReportFactory->generateReportData($teamName);
                
                // Add backlog data
                $backlogCount = rand(3, 10);
                $backlogBugs = [];
                $backlogTotalPoints = 0;
                
                for ($i = 0; $i < $backlogCount; $i++) {
                    $priority = $savedReportFactory->getRandomPriority();
                    $points = rand(1, 8);
                    $backlogTotalPoints += $points;
                    
                    $backlogBugs[] = [
                        'id' => 'BUG-' . (2000 + $i),
                        'name' => 'Backlog: ' . $savedReportFactory->getRandomBugTitle(),
                        'url' => 'https://trello.com/c/' . substr(md5(uniqid()), 0, 8),
                        'points' => $points,
                        'assigned' => 'Backlog User',
                        'labels' => ['Bug', $priority, 'Backlog'],
                        'team' => $teamName,
                        'sprint_origin' => $sprint->sprint_number > 1 ? $sprint->sprint_number - 1 : 1,
                        'status' => 'active',
                    ];
                }
                
                // Update backlog data in the report
                if (isset($reportData['backlog'])) {
                    $reportData['backlog'][$teamName] = $backlogBugs;
                    $reportData['backlogBugCount'] = $backlogCount . ' ' . ($backlogCount === 1 ? 'bug' : 'bugs');
                    $reportData['backlogTotalPoints'] = $backlogTotalPoints;
                } else {
                    $reportData['backlog'] = [$teamName => $backlogBugs];
                    $reportData['backlogBugCount'] = $backlogCount . ' ' . ($backlogCount === 1 ? 'bug' : 'bugs');
                    $reportData['backlogTotalPoints'] = $backlogTotalPoints;
                }
                
                // Create the saved report
                $savedReport = new SavedReport();
                $savedReport->user_id = $admin->id;
                $savedReport->sprint_id = $sprint->id;
                $savedReport->name = "Sprint {$sprint->sprint_number}: {$teamName} Report";
                $savedReport->report_data = $reportData;
                $savedReport->created_at = now();
                $savedReport->updated_at = now();
                $savedReport->save();
                
                $this->command->info("Created saved report for {$teamName} in Sprint #{$sprint->sprint_number}");
            }
        }
        
        $this->command->info('Saved reports creation completed');
    }
} 