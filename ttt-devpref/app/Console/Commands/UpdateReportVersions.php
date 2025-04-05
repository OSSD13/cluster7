<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SprintReport;
use App\Models\Sprint;
use Illuminate\Support\Facades\DB;

class UpdateReportVersions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reports:update-versions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update all report versions to use auto-incrementing versioning';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to update report versions...');
        
        // Get all team names from reports
        $teams = SprintReport::select('board_name')
            ->distinct()
            ->whereNotNull('board_name')
            ->get()
            ->pluck('board_name');
            
        $this->info("Found " . $teams->count() . " teams with reports.");
        
        $updatedCount = 0;
        
        // Get all sprints
        $sprints = Sprint::orderBy('sprint_number')->get();
        $this->info("Found " . $sprints->count() . " sprints.");
        
        foreach ($teams as $teamName) {
            $this->info("Processing team: {$teamName}");
            
            foreach ($sprints as $sprint) {
                // Get all reports for this team and sprint ordered by creation date (oldest first)
                $reports = SprintReport::where('board_name', $teamName)
                    ->where('sprint_id', $sprint->id)
                    ->orderBy('created_at', 'asc')
                    ->get();
                    
                if ($reports->count() == 0) {
                    continue; // Skip to next sprint if no reports
                }
                
                $this->info("  Found " . $reports->count() . " reports for Sprint #{$sprint->sprint_number}");
                
                // Assign incrementing version numbers starting from v1 for each sprint (oldest first)
                $versionNumber = 1;
                foreach ($reports as $report) {
                    $versionString = "v{$versionNumber}";
                    
                    // Extract the base report name without any version
                    $baseName = preg_replace('/\s+v\d+$/', '', $report->report_name);
                    $newReportName = $baseName . " {$versionString}";
                    
                    if ($newReportName !== $report->report_name) {
                        $this->line("  Updating report ID {$report->id}: '{$report->report_name}' → '{$newReportName}'");
                        $report->report_name = $newReportName;
                        $report->save();
                        $updatedCount++;
                    }
                    
                    $versionNumber++;
                }
            }
        }
        
        $this->info("Updated {$updatedCount} report names with new version numbers.");
        
        return Command::SUCCESS;
    }
} 