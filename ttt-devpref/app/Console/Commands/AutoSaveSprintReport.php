<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Setting;
use App\Models\Sprint;
use App\Models\SprintReport;
use App\Models\SavedReport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AutoSaveSprintReport extends Command
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
    protected $description = 'Automatically save sprint reports based on settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting auto-save sprint report process...');
        
        try {
            // Check if auto-save is enabled
            $autoSaveEnabled = $this->getSetting('auto_save_enabled', true);
            
            if (!$autoSaveEnabled) {
                $this->info('Auto-save is disabled in settings. Exiting.');
                return 0;
            }
            
            // Get current sprint settings
            $sprintDuration = (int)$this->getSetting('sprint_duration', 7);
            $sprintStartDay = (int)$this->getSetting('sprint_start_day', 1);
            $sprintEndTime = $this->getSetting('sprint_end_time', '18:00');
            
            // Get or create the current sprint
            $currentSprint = $this->getCurrentOrCreateSprint($sprintDuration, $sprintStartDay);
            
            if (!$currentSprint) {
                $this->error('Failed to determine the current sprint. Exiting.');
                return 1;
            }
            
            // Get the latest saved report to use as data source
            $latestReport = SavedReport::latest()->first();
            
            if (!$latestReport) {
                $this->warn('No saved reports found to use as data source. Exiting.');
                return 1;
            }
            
            DB::beginTransaction();
            
            // Create a new sprint report with data from the latest saved report
            $sprintReport = SprintReport::create([
                'sprint_id' => $currentSprint->id,
                'user_id' => $latestReport->user_id,
                'board_id' => $latestReport->board_id,
                'board_name' => $latestReport->board_name,
                'report_name' => "Sprint {$currentSprint->sprint_number} Report - Auto Generated",
                'notes' => "Automatically generated report for Sprint {$currentSprint->sprint_number}",
                'story_points_data' => $latestReport->story_points_data,
                'bug_cards_data' => $latestReport->bug_cards_data,
                'is_auto_generated' => true,
            ]);
            
            // Check if this is the end of the sprint
            $now = Carbon::now();
            $isEndOfSprint = $now->startOfDay()->eq($currentSprint->end_date->startOfDay());
            
            if ($isEndOfSprint) {
                // Set the current sprint status to completed
                $currentSprint->status = 'completed';
                $currentSprint->save();
                
                // Calculate the next sprint
                $nextSprintNumber = $currentSprint->sprint_number + 1;
                $nextSprintStart = $currentSprint->end_date->copy()->addDay();
                $nextSprintEnd = $nextSprintStart->copy()->addDays($sprintDuration - 1);
                
                // Create the next sprint
                Sprint::create([
                    'sprint_number' => $nextSprintNumber,
                    'start_date' => $nextSprintStart,
                    'end_date' => $nextSprintEnd,
                    'duration' => $sprintDuration,
                    'status' => 'planned',
                    'progress_percentage' => 0,
                    'days_elapsed' => 0,
                    'days_remaining' => $sprintDuration,
                ]);
                
                $this->info("End of sprint detected. Created next sprint #{$nextSprintNumber}");
            }
            
            DB::commit();
            
            $this->info("Successfully created sprint report with ID: {$sprintReport->id}");
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error in auto-save sprint report: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->error("Error: {$e->getMessage()}");
            return 1;
        }
    }
    
    /**
     * Get current sprint or create it if it doesn't exist
     *
     * @param int $sprintDuration
     * @param int $sprintStartDay
     * @return Sprint|null
     */
    private function getCurrentOrCreateSprint($sprintDuration, $sprintStartDay)
    {
        // Try to get the current sprint
        $currentSprint = Sprint::getCurrentSprint();
        
        if (!$currentSprint) {
            // Calculate sprint data
            $sprintNumber = $this->getCurrentSprintNumber();
            $sprintStartDate = $this->getCurrentSprintStartDate($sprintStartDay, $sprintDuration);
            $sprintEndDate = $sprintStartDate->copy()->addDays($sprintDuration - 1);
            
            // Create the sprint
            $currentSprint = Sprint::create([
                'sprint_number' => $sprintNumber,
                'start_date' => $sprintStartDate,
                'end_date' => $sprintEndDate,
                'duration' => $sprintDuration,
                'status' => 'active',
                'progress_percentage' => 0, // Will be updated by updateProgressForActiveSprints
                'days_elapsed' => 0, // Will be updated by updateProgressForActiveSprints
                'days_remaining' => 0, // Will be updated by updateProgressForActiveSprints
            ]);
            
            // Update progress for the newly created sprint
            Sprint::updateProgressForActiveSprints();
        }
        
        return $currentSprint;
    }
    
    /**
     * Get setting value with default
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private function getSetting($key, $default = null)
    {
        return Setting::get($key, $default);
    }
    
    /**
     * Get the current sprint number
     *
     * @return int
     */
    private function getCurrentSprintNumber()
    {
        // Check if there's a custom sprint number set
        $customSprintNumber = $this->getSetting('custom_sprint_number');
        
        if ($customSprintNumber !== null) {
            return (int)$customSprintNumber;
        }
        
        // Use the Sprint model's getNextSprintNumber method to auto-increment
        // This ensures consistent sprint numbering across the application
        return Sprint::getNextSprintNumber();
    }
    
    /**
     * Calculate the current sprint start date
     *
     * @param int $startDay
     * @param int $duration
     * @return Carbon
     */
    private function getCurrentSprintStartDate($startDay, $duration)
    {
        $now = Carbon::now();
        $currentYear = $now->year;
        
        // Ensure duration is at least 7 days
        $duration = max(7, (int)$duration);
        
        // Start with January 1st of the current year
        $yearStart = Carbon::createFromDate($currentYear, 1, 1)->startOfDay();
        
        // Find the first occurrence of the specified start day
        $firstSprintStart = $yearStart->copy();
        
        // Only adjust the day if we're using weekly boundaries
        if ($startDay >= 0 && $startDay <= 6) {
            while ($firstSprintStart->dayOfWeek != $startDay) {
                $firstSprintStart->addDay();
            }
        }
        
        // If January 1st is before the first sprint day of that week,
        // we start on January 1st (for simplicity)
        if ($yearStart->lt($firstSprintStart) && $firstSprintStart->diffInDays($yearStart) > 3) {
            $firstSprintStart = $yearStart;
        }
        
        // Calculate days since the first sprint start
        $daysSinceStart = $firstSprintStart->diffInDays($now);
        
        // Calculate which sprint we're in (0-based index)
        $currentSprintIndex = floor($daysSinceStart / $duration);
        
        // Calculate the start date of the current sprint
        $daysToAdd = $currentSprintIndex * $duration;
        $currentSprintStart = $firstSprintStart->copy()->addDays($daysToAdd);
        
        // Make sure the current sprint start doesn't exceed today
        $currentSprintEnd = $currentSprintStart->copy()->addDays($duration - 1);
        
        // If current date is after the end of the sprint, move to next sprint
        if ($now->startOfDay()->gt($currentSprintEnd)) {
            $currentSprintStart = $currentSprintStart->copy()->addDays($duration);
        }
        
        return $currentSprintStart;
    }
} 