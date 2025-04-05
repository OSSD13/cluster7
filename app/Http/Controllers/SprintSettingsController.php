<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Sprint;
use App\Models\SprintReport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SprintSettingsController extends Controller
{
    /**
     * Display the sprint settings page.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get current sprint settings
        $sprintDuration = (int)$this->getSetting('sprint_duration', 7); // Default to 7 days
        $sprintStartDay = $this->getSetting('sprint_start_day', 1); // Monday default
        $sprintEndTime = $this->getSetting('sprint_end_time', '18:00');
        $autoSaveEnabled = (bool)$this->getSetting('auto_save_enabled', true);
        
        // Get current sprint info
        $currentWeekNumber = Carbon::now()->weekOfYear;
        $currentSprintNumber = $this->getCurrentSprintNumber();
        
        // Calculate next report date
        $nextReportDate = $this->getNextReportDate($sprintStartDay, $sprintDuration, $sprintEndTime);
        
        // Get current sprint from database if it exists, otherwise calculate it
        $currentSprint = Sprint::getCurrentSprint();
        
        if ($currentSprint) {
            // Format dates using DateHelper for consistent formatting in 24-hour format
            $currentSprintStartDate = \App\Helpers\DateHelper::formatSprintDate($currentSprint->start_date);
            $currentSprintEndDate = \App\Helpers\DateHelper::formatSprintDate($currentSprint->end_date);
            $sprintProgressPercent = $currentSprint->progress_percentage;
            $daysElapsed = $currentSprint->days_elapsed;
            $daysRemaining = $currentSprint->days_remaining;
        } else {
            // Calculate sprint timeline data
            $now = Carbon::now();
            
            // Calculate current sprint start date based on settings
            $sprintStartDate = $this->getCurrentSprintStartDate($sprintStartDay, $sprintDuration);
            $currentSprintStartDate = \App\Helpers\DateHelper::formatSprintDate($sprintStartDate);
            
            // Calculate sprint end date (duration - 1 days after start)
            $sprintEndDate = $sprintStartDate->copy()->addDays($sprintDuration - 1);
            $currentSprintEndDate = \App\Helpers\DateHelper::formatSprintDate($sprintEndDate);
            
            // Calculate progress percentage
            $totalDays = $sprintDuration;
            
            // Calculate days elapsed since sprint start
            $daysElapsed = 0;
            if ($now->startOfDay()->gte($sprintStartDate->startOfDay())) {
                // If today is after or equal to sprint start date
                $daysElapsed = $sprintStartDate->startOfDay()->diffInDays($now->startOfDay()) + 1; // +1 to include current day
            }
            
            // Ensure days elapsed doesn't exceed the total sprint duration
            $daysElapsed = min($daysElapsed, $totalDays);
            
            // Calculate sprint progress percentage
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
            
            // Save the current sprint in the database
            $this->saveCurrentSprint($currentSprintNumber, $sprintStartDate, $sprintEndDate, $sprintDuration, $sprintProgressPercent, $daysElapsed, $daysRemaining);
        }
        
        return view('settings.sprint-settings', compact(
            'sprintDuration',
            'sprintStartDay',
            'sprintEndTime',
            'autoSaveEnabled',
            'currentWeekNumber',
            'currentSprintNumber',
            'nextReportDate',
            'currentSprintStartDate',
            'currentSprintEndDate',
            'sprintProgressPercent',
            'daysElapsed',
            'daysRemaining'
        ));
    }
    
    /**
     * Save current sprint data to the database
     *
     * @param int $sprintNumber
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param int $duration
     * @param float $progressPercentage
     * @param int $daysElapsed
     * @param int $daysRemaining
     * @return Sprint
     */
    private function saveCurrentSprint($sprintNumber, $startDate, $endDate, $duration, $progressPercentage, $daysElapsed, $daysRemaining)
    {
        $now = Carbon::now();
        $status = 'active';
        
        if ($now->lt($startDate)) {
            $status = 'planned';
        } elseif ($now->gt($endDate)) {
            $status = 'completed';
        }
        
        // Only use sprint_number as the unique identifier
        return Sprint::updateOrCreate(
            [
                'sprint_number' => $sprintNumber,
            ],
            [
                'start_date' => $startDate,
                'end_date' => $endDate,
                'duration' => $duration,
                'status' => $status,
                'progress_percentage' => $progressPercentage,
                'days_elapsed' => $daysElapsed,
                'days_remaining' => $daysRemaining,
            ]
        );
    }
    
    /**
     * Update sprint settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'sprint_duration' => 'required|in:7,14,21,28',
            'sprint_start_day' => 'required|in:0,1,2,3,4,5,6',
            'sprint_end_time' => 'required|in:16:00,17:00,18:00,23:59',
            'auto_save_enabled' => 'required|boolean',
        ]);
        
        // Update settings
        foreach ($validated as $key => $value) {
            $this->updateSetting($key, $value);
        }
        
        // Update scheduled task timing if needed
        if ($validated['auto_save_enabled']) {
            // This would ideally update the scheduled task timing, but since Laravel schedules
            // are defined in code, we can only provide feedback for now
            // In a production app, this could update a dynamic schedule config
        }
        
        // Recalculate current sprint with new settings
        $currentSprintNumber = $this->getCurrentSprintNumber();
        $sprintStartDate = $this->getCurrentSprintStartDate($validated['sprint_start_day'], $validated['sprint_duration']);
        $sprintEndDate = $sprintStartDate->copy()->addDays($validated['sprint_duration'] - 1);
        
        // Update or create the current sprint
        $this->saveCurrentSprint(
            $currentSprintNumber,
            $sprintStartDate,
            $sprintEndDate,
            $validated['sprint_duration'],
            0, // Progress will be recalculated
            0, // Days elapsed will be recalculated
            0  // Days remaining will be recalculated
        );
        
        // Update progress for all active sprints
        Sprint::updateProgressForActiveSprints();
        
        return redirect()->route('settings.sprint')->with('success', 'Sprint settings updated successfully.');
    }
    
    /**
     * Generate sprint reports manually.
     *
     * @return \Illuminate\Http\Response
     */
    public function generateNow()
    {
        try {
            DB::beginTransaction();
            
            // Run the command to generate sprint reports
            $exitCode = Artisan::call('reports:auto-save-sprint');
            
            // Get the current sprint
            $currentSprint = Sprint::getCurrentSprint();
            
            if (!$currentSprint) {
                // Calculate the current sprint and save it if it doesn't exist
                $sprintDuration = (int)$this->getSetting('sprint_duration', 7);
                $sprintStartDay = $this->getSetting('sprint_start_day', 1);
                $currentSprintNumber = $this->getCurrentSprintNumber();
                $sprintStartDate = $this->getCurrentSprintStartDate($sprintStartDay, $sprintDuration);
                $sprintEndDate = $sprintStartDate->copy()->addDays($sprintDuration - 1);
                
                $currentSprint = $this->saveCurrentSprint(
                    $currentSprintNumber,
                    $sprintStartDate,
                    $sprintEndDate,
                    $sprintDuration,
                    0, // Will be updated by updateProgressForActiveSprints
                    0, // Will be updated by updateProgressForActiveSprints
                    0  // Will be updated by updateProgressForActiveSprints
                );
                
                Sprint::updateProgressForActiveSprints();
            }
            
            // Add the generated report to the sprint
            $latestReport = $this->getLatestReport();
            if ($latestReport && $currentSprint) {
                SprintReport::create([
                    'sprint_id' => $currentSprint->id,
                    'user_id' => auth()->id(),
                    'board_id' => $latestReport->board_id ?? null,
                    'board_name' => $latestReport->board_name ?? null,
                    'report_name' => "Sprint {$currentSprint->sprint_number} Report - Manual",
                    'story_points_data' => $latestReport->story_points_data ?? null,
                    'bug_cards_data' => $latestReport->bug_cards_data ?? null,
                    'is_auto_generated' => false,
                ]);
            }
            
            DB::commit();
            
            if ($exitCode === 0) {
                return redirect()->route('settings.sprint')->with('success', 'Sprint reports generated successfully.');
            } else {
                return redirect()->route('settings.sprint')->with('error', 'Failed to generate sprint reports. Check the logs for details.');
            }
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error generating sprint report: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('settings.sprint')->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }
    
    /**
     * Get the latest saved report
     * 
     * @return \App\Models\SavedReport|null
     */
    private function getLatestReport()
    {
        return \App\Models\SavedReport::latest()->first();
    }
    
    /**
     * Get setting value with default.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    private function getSetting($key, $default = null)
    {
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
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
        Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }
    
    /**
     * Manually set the current sprint number
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function setCurrentSprint(Request $request)
    {
        $validated = $request->validate([
            'current_sprint_number' => 'required|integer|min:1',
        ]);
        
        // Store the custom sprint number in settings
        $this->setCustomSprintNumber($validated['current_sprint_number']);
        
        // Update the current sprint in the database
        $sprintDuration = (int)$this->getSetting('sprint_duration', 7);
        $sprintStartDay = $this->getSetting('sprint_start_day', 1);
        $sprintStartDate = $this->getCurrentSprintStartDate($sprintStartDay, $sprintDuration);
        $sprintEndDate = $sprintStartDate->copy()->addDays($sprintDuration - 1);
        
        $this->saveCurrentSprint(
            $validated['current_sprint_number'],
            $sprintStartDate,
            $sprintEndDate,
            $sprintDuration,
            0, // Will be updated by updateProgressForActiveSprints
            0, // Will be updated by updateProgressForActiveSprints
            0  // Will be updated by updateProgressForActiveSprints
        );
        
        Sprint::updateProgressForActiveSprints();
        
        return redirect()->route('settings.sprint')->with('success', 'Current sprint number has been manually set to ' . $validated['current_sprint_number'] . '.');
    }
    
    /**
     * Get the current sprint number based on custom setting or calculated value
     *
     * @return int
     */
    public function getCurrentSprintNumber()
    {
        // Check if there's a custom sprint number set
        $customSprintNumber = $this->getSetting('custom_sprint_number');
        
        if ($customSprintNumber !== null) {
            return (int)$customSprintNumber;
        }
        
        // Check for existing sprints in the database
        $lastSprint = Sprint::orderBy('sprint_number', 'desc')->first();
        
        if ($lastSprint) {
            // If the last sprint has ended, the current sprint should be one higher
            $now = Carbon::now();
            if ($lastSprint->end_date->lt($now)) {
                return $lastSprint->sprint_number + 1;
            }
            
            // Otherwise, return the current highest sprint number
            return $lastSprint->sprint_number;
        }
        
        // If no sprints exist in the database, start from Sprint #1
        // For new installations or when no sprint data exists
        $calculatedNumber = $this->calculateSprintNumber();
        
        // Never return a sprint number less than 1
        return max(1, $calculatedNumber);
    }
    
    /**
     * Calculate the sprint number based on weeks since the start of the year
     * This is a fallback method when no sprints exist in the database
     *
     * @return int
     */
    private function calculateSprintNumber()
    {
        // Calculate it normally
        $now = Carbon::now();
        $currentYear = $now->year;
        
        // Get sprint duration from settings
        $sprintDuration = (int)$this->getSetting('sprint_duration', 7);
        
        // Ensure duration is at least 7 days to prevent division by zero
        $sprintDuration = max(7, $sprintDuration);
        $sprintDurationInWeeks = $sprintDuration / 7;
        
        // Start with January 1st of the current year
        $yearStart = Carbon::createFromDate($currentYear, 1, 1)->startOfDay();
        
        // Get the sprint start day from settings (default to Monday)
        $sprintStartDay = (int)$this->getSetting('sprint_start_day', 1);
        
        // Find the first occurrence of the specified start day
        $firstSprintStart = $yearStart->copy();
        while ($firstSprintStart->dayOfWeek != $sprintStartDay) {
            $firstSprintStart->addDay();
        }
        
        // If January 1st is before the first sprint day of that week,
        // the first partial week counts as sprint 1
        if ($yearStart->lt($firstSprintStart)) {
            $firstSprintStart = $yearStart;
        }
        
        // Calculate weeks since the first sprint start
        $weeksSinceStart = $firstSprintStart->diffInWeeks($now);
        
        // The sprint number is the number of complete weeks / sprint duration in weeks + 1
        $sprintNumber = (int)(floor($weeksSinceStart / $sprintDurationInWeeks) + 1);
        
        return $sprintNumber;
    }
    
    /**
     * Set a custom sprint number
     *
     * @param int $sprintNumber
     * @return void
     */
    private function setCustomSprintNumber($sprintNumber)
    {
        $this->updateSetting('custom_sprint_number', $sprintNumber);
    }
    
    /**
     * Calculate the next report date based on settings.
     *
     * @param int $startDay Day of week (0=Sunday, 6=Saturday)
     * @param int $duration Sprint duration in days (7, 14, 21, or 28)
     * @param string $endTime Time in HH:MM format
     * @return string Formatted date
     */
    public function getNextReportDate($startDay, $duration, $endTime)
    {
        $now = Carbon::now();
        
        // Calculate current sprint start date
        $currentSprintStart = $this->getCurrentSprintStartDate($startDay, $duration);
        
        // Sprint end is always duration-1 days after start
        $currentSprintEnd = $currentSprintStart->copy()->addDays($duration - 1);
        
        // Add time component to the end date
        list($hours, $minutes) = explode(':', $endTime);
        $reportDateTime = $currentSprintEnd->copy()
            ->setHour((int)$hours)
            ->setMinute((int)$minutes)
            ->setSecond(0);
        
        // If we're past the current sprint's end time, calculate the next sprint's end date
        if ($now->gt($reportDateTime)) {
            $nextSprintStart = $currentSprintStart->copy()->addDays($duration);
            $nextSprintEnd = $nextSprintStart->copy()->addDays($duration - 1);
            $reportDateTime = $nextSprintEnd->setHour((int)$hours)
                ->setMinute((int)$minutes)
                ->setSecond(0);
        }
        
        // Return date formatted with 24-hour time format (H:i)
        return $reportDateTime->format('F j, Y \a\t H:i');
    }
    
    /**
     * Calculate the current sprint start date based on settings.
     * Sprints are calculated from January 1st of the current year,
     * with each sprint being of configurable duration.
     *
     * @param int $startDay Day of week (0=Sunday, 6=Saturday)
     * @param int $duration Sprint duration in days
     * @return Carbon
     */
    public function getCurrentSprintStartDate($startDay, $duration)
    {
        try {
            $now = Carbon::now();
            $currentYear = $now->year;
            
            // Ensure duration is at least 7 days to prevent division by zero
            $duration = max(7, (int)$duration);
            $durationInWeeks = $duration / 7;
            
            // Start with January 1st of the current year
            $yearStart = Carbon::createFromDate($currentYear, 1, 1)->startOfDay();
            
            // Find the first occurrence of the specified start day (e.g., Monday)
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
                $currentSprintEnd = $currentSprintStart->copy()->addDays($duration - 1);
            }
            
            // Log the calculation for debugging
            \Log::debug('Sprint date calculation', [
                'now' => $now->format('Y-m-d'),
                'yearStart' => $yearStart->format('Y-m-d'),
                'firstSprintStart' => $firstSprintStart->format('Y-m-d'),
                'daysSinceStart' => $daysSinceStart,
                'durationInWeeks' => $durationInWeeks,
                'currentSprintIndex' => $currentSprintIndex,
                'daysToAdd' => $daysToAdd,
                'currentSprintStart' => $currentSprintStart->format('Y-m-d'),
                'currentSprintEnd' => $currentSprintEnd->format('Y-m-d'),
                'duration' => $duration
            ]);
            
            return $currentSprintStart;
        } catch (\Exception $e) {
            \Log::error('Error in getCurrentSprintStartDate: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Fallback to start of current week if there's an error
            return Carbon::now()->startOfWeek();
        }
    }
} 