<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Console\Commands\CacheTrelloData;
use App\Console\Commands\OptimizeApp;
use App\Console\Commands\AutoSaveSprintReport;
use App\Models\Setting;
use App\Console\Commands\SeedSprintsCommand;
use App\Console\Commands\SeedCustomSprintsCommand;
use App\Console\Commands\SeedOptimizedReportsCommand;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        CacheTrelloData::class,
        OptimizeApp::class,
        AutoSaveSprintReport::class,
        SeedSprintsCommand::class,
        SeedCustomSprintsCommand::class,
        SeedOptimizedReportsCommand::class,
        \App\Console\Commands\FixReportNames::class,
        \App\Console\Commands\UpdateReportVersions::class,
    ];
    
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Run the Trello data cache command every hour to keep data fresh
        $schedule->command('trello:cache')
                ->hourly()
                ->withoutOverlapping()
                ->runInBackground()
                ->onOneServer();
                
        // Run optimization weekly during low-traffic hours
        $schedule->command('app:optimize')
                ->weekly()
                ->sundays()
                ->at('01:00')
                ->onOneServer();
                
        // Set up the auto save sprint report schedule based on settings
        $this->scheduleSprintReport($schedule);
    }
    
    /**
     * Schedule the sprint report command based on settings
     */
    private function scheduleSprintReport(Schedule $schedule): void
    {
        // Check if auto-save is enabled
        $autoSaveEnabled = Setting::get('auto_save_enabled', true);
        
        if (!$autoSaveEnabled) {
            return;
        }
        
        // Get sprint settings
        $sprintStartDay = (int)Setting::get('sprint_start_day', 1); // Default: Monday
        $sprintDuration = (int)Setting::get('sprint_duration', 7); // Default: 7 days
        $sprintEndTime = Setting::get('sprint_end_time', '18:00'); // Default: 6 PM
        
        // Calculate the day of the week for the end of the sprint
        $endSprintDay = ($sprintStartDay + $sprintDuration - 1) % 7;
        
        // Map numeric day to day name for the scheduler
        $dayMap = [
            0 => 'sundays',
            1 => 'mondays',
            2 => 'tuesdays',
            3 => 'wednesdays',
            4 => 'thursdays',
            5 => 'fridays',
            6 => 'saturdays'
        ];
        
        $dayMethod = $dayMap[$endSprintDay];
        
        // Schedule the command to run on the end day of each sprint at the configured time
        $schedule->command('reports:auto-save-sprint')
                ->weekly()
                ->{$dayMethod}()
                ->at($sprintEndTime)
                ->withoutOverlapping()
                ->onOneServer();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}