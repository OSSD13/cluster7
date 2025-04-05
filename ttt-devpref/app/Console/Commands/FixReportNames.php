<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SprintReport;
use App\Models\Sprint;

class FixReportNames extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:fix-names';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix all report names with "Sprint Unknown" by replacing with actual sprint numbers';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to fix report names...');
        
        // Get all reports that have "Unknown" in the name
        $reports = SprintReport::where('report_name', 'like', '%Unknown%')->get();
        
        $this->info("Found {$reports->count()} reports with 'Unknown' in the name.");
        
        $updated = 0;
        
        foreach ($reports as $report) {
            // Get the sprint associated with this report
            $sprint = $report->sprint;
            
            // If no sprint is associated, try to find one
            if (!$sprint && $report->sprint_id) {
                $sprint = Sprint::find($report->sprint_id);
            }
            
            // If we found a sprint, update the report name
            if ($sprint) {
                $originalName = $report->report_name;
                $report->report_name = str_replace('Sprint Unknown', "Sprint {$sprint->sprint_number}", $report->report_name);
                
                if ($originalName !== $report->report_name) {
                    $this->line("Updating report ID {$report->id}: '{$originalName}' → '{$report->report_name}'");
                    $report->save();
                    $updated++;
                }
            } else {
                $this->warn("Report ID {$report->id} has no associated sprint. Unable to fix name.");
            }
        }
        
        $this->info("Updated {$updated} report names successfully.");
        
        return Command::SUCCESS;
    }
} 