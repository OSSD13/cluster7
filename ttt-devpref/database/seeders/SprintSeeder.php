<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Sprint;
use App\Models\SprintReport;
use App\Models\User;
use Illuminate\Support\Carbon;

class SprintSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Seeding sprints...');
        
        // Create sequential sprints with varying statuses
        $sprints = collect([
            // Completed sprints (2)
            Sprint::factory()->completed()->create([
                'sprint_number' => 1,
                'duration' => 14,
            ]),
            Sprint::factory()->completed()->create([
                'sprint_number' => 2,
                'duration' => 14,
            ]),
            
            // Active sprint (1)
            Sprint::factory()->active()->create([
                'sprint_number' => 3,
                'duration' => 14,
            ]),
            
            // Planned sprints (2)
            Sprint::factory()->planned()->create([
                'sprint_number' => 4,
                'duration' => 14,
            ]),
            Sprint::factory()->planned()->create([
                'sprint_number' => 5,
                'duration' => 14,
            ]),
        ]);
        
        $this->command->info('Created ' . $sprints->count() . ' sprints');
        
        // Make sure we have at least one user to associate with reports
        $user = User::first() ?: User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
        
        $this->command->info('Seeding sprint reports...');
        
        // Teams that will have reports
        $teams = ['Frontend Team', 'Backend Team', 'Design Team', 'DevOps Team', 'QA Team'];
        
        // Create 2-4 reports for each sprint
        $sprints->each(function ($sprint) use ($teams, $user) {
            // For each sprint, create reports for 2-3 random teams
            $selectedTeams = collect($teams)->random(rand(2, 3))->all();
            
            foreach ($selectedTeams as $team) {
                // Create 1-3 reports per team (for version history)
                $reportsCount = rand(1, 3);
                
                // Create reports with different dates to simulate version history
                $startDate = $sprint->start_date->copy();
                
                for ($i = 0; $i < $reportsCount; $i++) {
                    // Each report is created a few days after the previous
                    $reportDate = $startDate->copy()->addDays(rand(1, 3));
                    
                    // Define versions (most recent first)
                    $version = $reportsCount - $i;
                    
                    // Create realistic report data
                    SprintReport::factory()->create([
                        'sprint_id' => $sprint->id,
                        'user_id' => $user->id,
                        'board_name' => $team,
                        'report_name' => "Sprint {$sprint->sprint_number} Report: {$team}" . ($i == 0 ? ' (Latest)' : " (v{$version})"),
                        'is_auto_generated' => rand(0, 1) > 0.7,
                        'created_at' => $reportDate,
                        'updated_at' => $reportDate,
                    ]);
                    
                    // Increment the start date for the next report
                    $startDate = $reportDate;
                }
            }
        });
        
        $reportCount = SprintReport::count();
        $this->command->info("Created {$reportCount} sprint reports across 5 sprints");
        
        // Update progress for all sprints
        Sprint::updateProgressForActiveSprints();
        $this->command->info('Updated progress for all active sprints');
    }
} 