<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\SprintReport;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix double-encoded JSON in sprint_reports table
        $reports = SprintReport::all();
        
        foreach ($reports as $report) {
            $updated = false;
            
            // Fix story_points_data if it exists and is a string
            if (!empty($report->story_points_data) && is_string($report->story_points_data)) {
                try {
                    // Attempt to decode the JSON to see if it's already decoded
                    $decoded = json_decode($report->story_points_data, true);
                    
                    // If decode was successful but the result is a string (not an array), 
                    // it might be double-encoded
                    if (json_last_error() === JSON_ERROR_NONE && is_string($decoded)) {
                        $doubleDecoded = json_decode($decoded, true);
                        
                        if (json_last_error() === JSON_ERROR_NONE) {
                            // Successfully double-decoded, update the record
                            $report->story_points_data = $doubleDecoded;
                            $updated = true;
                        }
                    }
                } catch (\Exception $e) {
                    // Log error but continue processing other records
                    \Log::error('Error fixing story_points_data for report #' . $report->id . ': ' . $e->getMessage());
                }
            }
            
            // Fix bug_cards_data if it exists and is a string
            if (!empty($report->bug_cards_data) && is_string($report->bug_cards_data)) {
                try {
                    // Attempt to decode the JSON to see if it's already decoded
                    $decoded = json_decode($report->bug_cards_data, true);
                    
                    // If decode was successful but the result is a string (not an array), 
                    // it might be double-encoded
                    if (json_last_error() === JSON_ERROR_NONE && is_string($decoded)) {
                        $doubleDecoded = json_decode($decoded, true);
                        
                        if (json_last_error() === JSON_ERROR_NONE) {
                            // Successfully double-decoded, update the record
                            $report->bug_cards_data = $doubleDecoded;
                            $updated = true;
                        }
                    }
                } catch (\Exception $e) {
                    // Log error but continue processing other records
                    \Log::error('Error fixing bug_cards_data for report #' . $report->id . ': ' . $e->getMessage());
                }
            }
            
            // Save the report if any fields were updated
            if ($updated) {
                $report->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to revert the data fix
    }
};
