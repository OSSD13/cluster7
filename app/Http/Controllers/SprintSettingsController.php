<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Sprint;
use App\Models\SprintReport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
        $now = Carbon::now();
        $currentWeekNumber = $now->weekOfYear;
        $currentSprintNumber = $this->getCurrentSprintNumber();

        // Calculate next report date
        $nextReportDate = $this->getNextReportDate($sprintStartDay, $sprintDuration, $sprintEndTime);

        // Get current sprint from database if it exists, otherwise calculate it
        $currentSprint = Sprint::getCurrentSprint();

        if ($currentSprint) {
            // Format dates using DateHelper for consistent formatting in 24-hour format
            $currentSprintStartDate = \App\Helpers\DateHelper::formatSprintDate($currentSprint->start_date);
            $currentSprintEndDate = \App\Helpers\DateHelper::formatSprintDate($currentSprint->end_date);

            // Recalculate progress based on current time
            $totalDays = $sprintDuration;

            // Calculate days elapsed including the current day if we're within the sprint
            $startDate = $currentSprint->start_date->startOfDay();
            $endDate = $currentSprint->end_date->endOfDay();
            $nowDate = $now->startOfDay();

            if ($nowDate->between($startDate, $endDate)) {
                $daysElapsed = $startDate->diffInDays($nowDate) + 1;
            } elseif ($nowDate->gt($endDate)) {
                $daysElapsed = $totalDays;
            } else {
                $daysElapsed = 0;
            }

            // Ensure days elapsed doesn't exceed sprint duration
            $daysElapsed = min($daysElapsed, $totalDays);

            // Calculate sprint progress percentage
            $sprintProgressPercent = min(100, round(($daysElapsed / $totalDays) * 100, 1));

            // Calculate days remaining with proper precision
            if ($nowDate->lte($endDate)) {
                // Calculate exact days remaining including partial days
                $daysRemaining = $nowDate->floatDiffInDays($endDate);
                // Round to 1 decimal place
                $daysRemaining = round($daysRemaining, 1);
                // If we're on the last day and have less than 0.1 days remaining, show 0
                if ($daysRemaining < 0.1) {
                    $daysRemaining = 0;
                }
            } else {
                $daysRemaining = 0;
            }

            // Update the sprint with new calculations
            $currentSprint->update([
                'progress_percentage' => $sprintProgressPercent,
                'days_elapsed' => $daysElapsed,
                'days_remaining' => $daysRemaining
            ]);
        } else {
            // Calculate sprint timeline data
            $sprintStartDate = $this->getCurrentSprintStartDate($sprintStartDay, $sprintDuration);
            $currentSprintStartDate = \App\Helpers\DateHelper::formatSprintDate($sprintStartDate);

            // Calculate sprint end date (duration - 1 days after start)
            $sprintEndDate = $sprintStartDate->copy()->addDays($sprintDuration - 1);
            $currentSprintEndDate = \App\Helpers\DateHelper::formatSprintDate($sprintEndDate);

            // Calculate progress percentage
            $totalDays = $sprintDuration;

            // Calculate days elapsed including the current day if we're within the sprint
            $startDate = $sprintStartDate->startOfDay();
            $endDate = $sprintEndDate->endOfDay();
            $nowDate = $now->startOfDay();

            if ($nowDate->between($startDate, $endDate)) {
                $daysElapsed = $startDate->diffInDays($nowDate) + 1;
            } elseif ($nowDate->gt($endDate)) {
                $daysElapsed = $totalDays;
            } else {
                $daysElapsed = 0;
            }

            // Ensure days elapsed doesn't exceed sprint duration
            $daysElapsed = min($daysElapsed, $totalDays);

            // Calculate sprint progress percentage
            $sprintProgressPercent = min(100, round(($daysElapsed / $totalDays) * 100, 1));

            // Calculate days remaining with proper precision
            if ($nowDate->lte($endDate)) {
                // Calculate exact days remaining including partial days
                $daysRemaining = $nowDate->floatDiffInDays($endDate);
                // Round to 1 decimal place
                $daysRemaining = round($daysRemaining, 1);
                // If we're on the last day and have less than 0.1 days remaining, show 0
                if ($daysRemaining < 0.1) {
                    $daysRemaining = 0;
                }
            } else {
                $daysRemaining = 0;
            }

            // Save the current sprint in the database
            $this->saveCurrentSprint(
                $currentSprintNumber,
                $sprintStartDate,
                $sprintEndDate,
                $sprintDuration,
                $sprintProgressPercent,
                $daysElapsed,
                $daysRemaining
            );
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
     * Update the sprint settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
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

        // Get the current sprint before updating
        $currentSprint = Sprint::getCurrentSprint();

        // If we have a current sprint, use its start date
        if ($currentSprint) {
            $sprintStartDate = $currentSprint->start_date;
        } else {
            // Otherwise calculate new start date
            $sprintStartDate = $this->getCurrentSprintStartDate($validated['sprint_start_day'], $validated['sprint_duration']);
        }

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

        // Use direct routing instead of redirect
        $sprintDuration = $this->getSetting('sprint_duration', 7);
        $sprintStartDay = $this->getSetting('sprint_start_day', 1);
        $sprintEndTime = $this->getSetting('sprint_end_time', '17:00');
        $autoSaveEnabled = $this->getSetting('auto_save_enabled', false);

        $nextSprintNumber = Sprint::getNextSprintNumber();
        $recentSprints = Sprint::orderBy('sprint_number', 'desc')->take(5)->get();

        // Calculate current week number
        $currentWeekNumber = Carbon::now()->weekOfYear;

        // Calculate next report date
        $nextReportDate = $this->getNextReportDate($sprintStartDay, $sprintDuration, $sprintEndTime);

        // Calculate sprint progress percentage
        $sprintProgressPercent = $currentSprint ? $currentSprint->progress_percentage : 0;

        // Calculate sprint start and end dates
        if ($currentSprint) {
            // Format dates using DateHelper for consistent formatting
            $currentSprintStartDate = \App\Helpers\DateHelper::formatSprintDate($currentSprint->start_date);
            $currentSprintEndDate = \App\Helpers\DateHelper::formatSprintDate($currentSprint->end_date);
            $daysElapsed = $currentSprint->days_elapsed;
            $daysRemaining = $currentSprint->days_remaining;
        } else {
            // Calculate manual dates if no sprint found
            $sprintStartDate = $this->getCurrentSprintStartDate($sprintStartDay, $sprintDuration);
            $currentSprintStartDate = \App\Helpers\DateHelper::formatSprintDate($sprintStartDate);
            $sprintEndDate = $sprintStartDate->copy()->addDays($sprintDuration - 1);
            $currentSprintEndDate = \App\Helpers\DateHelper::formatSprintDate($sprintEndDate);
            $daysElapsed = 0;
            $daysRemaining = $sprintDuration;
        }

        return view('settings.sprint-settings', [
            'sprintDuration' => $sprintDuration,
            'sprintStartDay' => $sprintStartDay,
            'sprintEndTime' => $sprintEndTime,
            'autoSaveEnabled' => $autoSaveEnabled,
            'currentSprint' => $currentSprint,
            'nextSprintNumber' => $nextSprintNumber,
            'recentSprints' => $recentSprints,
            'currentSprintNumber' => $currentSprintNumber,
            'currentWeekNumber' => $currentWeekNumber,
            'nextReportDate' => $nextReportDate,
            'sprintProgressPercent' => $sprintProgressPercent,
            'currentSprintStartDate' => $currentSprintStartDate,
            'currentSprintEndDate' => $currentSprintEndDate,
            'daysElapsed' => $daysElapsed,
            'daysRemaining' => $daysRemaining,
            'success' => 'Sprint settings updated successfully.'
        ]);
    }

    /**
     * Generate sprint reports manually.
     *
     * @return \Illuminate\View\View
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

            // Use direct routing instead of redirect
            $sprintDuration = $this->getSetting('sprint_duration', 7);
            $sprintStartDay = $this->getSetting('sprint_start_day', 1);
            $sprintEndTime = $this->getSetting('sprint_end_time', '17:00');
            $autoSaveEnabled = $this->getSetting('auto_save_enabled', false);

            $nextSprintNumber = Sprint::getNextSprintNumber();
            $recentSprints = Sprint::orderBy('sprint_number', 'desc')->take(5)->get();

            $message = $exitCode === 0 ?
                'Sprint reports generated successfully.' :
                'Failed to generate sprint reports. Check the logs for details.';

            return view('settings.sprint-settings', [
                'sprintDuration' => $sprintDuration,
                'sprintStartDay' => $sprintStartDay,
                'sprintEndTime' => $sprintEndTime,
                'autoSaveEnabled' => $autoSaveEnabled,
                'currentSprint' => $currentSprint,
                'nextSprintNumber' => $nextSprintNumber,
                'recentSprints' => $recentSprints,
                'success' => $exitCode === 0 ? $message : null,
                'error' => $exitCode !== 0 ? $message : null
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error generating sprint report: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            // Use direct routing instead of redirect
            $sprintDuration = $this->getSetting('sprint_duration', 7);
            $sprintStartDay = $this->getSetting('sprint_start_day', 1);
            $sprintEndTime = $this->getSetting('sprint_end_time', '17:00');
            $autoSaveEnabled = $this->getSetting('auto_save_enabled', false);

            $currentSprint = Sprint::getCurrentSprint();
            $nextSprintNumber = Sprint::getNextSprintNumber();
            $recentSprints = Sprint::orderBy('sprint_number', 'desc')->take(5)->get();

            return view('settings.sprint-settings', [
                'sprintDuration' => $sprintDuration,
                'sprintStartDay' => $sprintStartDay,
                'sprintEndTime' => $sprintEndTime,
                'autoSaveEnabled' => $autoSaveEnabled,
                'currentSprint' => $currentSprint,
                'nextSprintNumber' => $nextSprintNumber,
                'recentSprints' => $recentSprints,
                'error' => 'An error occurred: ' . $e->getMessage()
            ]);
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
     * @return \Illuminate\View\View
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

        // Use direct routing instead of redirect
        $sprintDuration = $this->getSetting('sprint_duration', 7);
        $sprintStartDay = $this->getSetting('sprint_start_day', 1);
        $sprintEndTime = $this->getSetting('sprint_end_time', '17:00');
        $autoSaveEnabled = $this->getSetting('auto_save_enabled', false);

        $currentSprint = Sprint::getCurrentSprint();
        $nextSprintNumber = Sprint::getNextSprintNumber();
        $recentSprints = Sprint::orderBy('sprint_number', 'desc')->take(5)->get();

        return view('settings.sprint-settings', [
            'sprintDuration' => $sprintDuration,
            'sprintStartDay' => $sprintStartDay,
            'sprintEndTime' => $sprintEndTime,
            'autoSaveEnabled' => $autoSaveEnabled,
            'currentSprint' => $currentSprint,
            'nextSprintNumber' => $nextSprintNumber,
            'recentSprints' => $recentSprints,
            'success' => 'Current sprint number has been manually set to ' . $validated['current_sprint_number'] . '.'
        ]);
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
     * Sprints are calculated from the end date backwards,
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

            // Ensure duration is at least 7 days and a multiple of 7 for proper week alignment
            $duration = max(7, (int)$duration);
            $weeks = $duration / 7;

            // Find the next occurrence of the start day
            $nextStartDate = $now->copy();
            while ($nextStartDate->dayOfWeek != $startDay) {
                $nextStartDate->addDay();
            }

            // Calculate the current sprint's start date by moving backwards
            $currentSprintStart = $nextStartDate->copy();

            // If we're in the middle of a sprint, move back to the start of the current sprint
            $weeksToSubtract = 0;
            while ($now->lt($currentSprintStart)) {
                $currentSprintStart->subDays(7);
            }
            while ($now->diffInDays($currentSprintStart, false) >= $duration) {
                $currentSprintStart->addDays(7);
            }

            // Calculate the end date
            $currentSprintEnd = $currentSprintStart->copy()->addDays($duration - 1);

            // If we're past the current sprint's end date, move to the next sprint
            if ($now->startOfDay()->gt($currentSprintEnd)) {
                $currentSprintStart = $currentSprintStart->addDays($duration);
                $currentSprintEnd = $currentSprintStart->copy()->addDays($duration - 1);
            }

            // Log the calculation for debugging
            \Log::debug('Sprint date calculation', [
                'now' => $now->format('Y-m-d'),
                'currentSprintStart' => $currentSprintStart->format('Y-m-d'),
                'currentSprintEnd' => $currentSprintEnd->format('Y-m-d'),
                'duration' => $duration,
                'weeks' => $weeks,
                'startDay' => $startDay
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
