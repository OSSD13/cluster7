<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class Sprint extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sprint_number',
        'start_date',
        'end_date',
        'duration',
        'status',
        'progress_percentage',
        'days_elapsed',
        'days_remaining',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'progress_percentage' => 'float',
        'days_elapsed' => 'integer',
        'days_remaining' => 'integer',
        'duration' => 'integer',
    ];

    /**
     * Get the reports associated with this sprint.
     */
    public function reports()
    {
        return $this->hasMany(SprintReport::class);
    }

    /**
     * Get the current sprint.
     *
     * @return Sprint|null
     */
    public static function getCurrentSprint()
    {
        $now = Carbon::now();
        
        // Try to find a sprint that covers the current date
        $currentSprint = self::where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->first();
            
        if (!$currentSprint) {
            // If no active sprint found, check if there are any sprints in the database
            $lastSprint = self::orderBy('sprint_number', 'desc')->first();
            
            if (!$lastSprint) {
                // If no sprints exist at all, return null (caller should create Sprint #1)
                return null;
            }
            
            // Check if the last sprint's end date is in the past
            if ($lastSprint->end_date->lt($now)) {
                // The most recent sprint has ended, so we would be in the next sprint
                // Return null to signal that a new sprint should be created
                return null;
            }
            
            // If we have a sprint that hasn't started yet, return it
            if ($lastSprint->start_date->gt($now)) {
                return $lastSprint;
            }
            
            // In any other case, return null to indicate no active sprint
            return null;
        }
        
        return $currentSprint;
    }
    
    /**
     * Get the next sprint number
     * 
     * @return int
     */
    public static function getNextSprintNumber()
    {
        // Get the highest sprint number currently in the database
        $highestSprintNumber = self::max('sprint_number');
        
        // If no sprints exist yet, start with sprint #1
        if (!$highestSprintNumber) {
            return 1;
        }
        
        // Get the sprint with the highest number
        $lastSprint = self::where('sprint_number', $highestSprintNumber)->first();
        
        // If the last sprint has ended, the next sprint should be one higher
        $now = Carbon::now();
        if ($lastSprint && $lastSprint->end_date->lt($now)) {
            return $lastSprint->sprint_number + 1;
        }
        
        // Otherwise, return the current highest sprint number
        // This handles cases where the current sprint is still active
        return $highestSprintNumber;
    }
    
    /**
     * Update progress percentages for all active sprints.
     */
    public static function updateProgressForActiveSprints()
    {
        $now = Carbon::now();
        $activeSprints = self::where('end_date', '>=', $now)->get();
        
        foreach ($activeSprints as $sprint) {
            $totalDays = $sprint->duration;
            
            // Calculate days elapsed since sprint start
            $daysElapsed = 0;
            if ($now->startOfDay()->gte($sprint->start_date->startOfDay())) {
                $daysElapsed = $sprint->start_date->startOfDay()->diffInDays($now->startOfDay()) + 1;
            }
            
            // Ensure days elapsed doesn't exceed the total sprint duration
            $daysElapsed = min($daysElapsed, $totalDays);
            
            // Calculate sprint progress percentage
            $sprintProgressPercent = min(100, round(($daysElapsed / $totalDays) * 100, 1));
            
            // Calculate days remaining in the sprint
            if ($now->startOfDay()->gt($sprint->end_date->startOfDay())) {
                $daysRemaining = 0;
            } else {
                $daysRemaining = $now->startOfDay()->diffInDays($sprint->end_date->startOfDay());
                if ($now->startOfDay()->eq($sprint->end_date->startOfDay())) {
                    $daysRemaining = 0;
                }
            }
            
            // Update the sprint
            $sprint->progress_percentage = $sprintProgressPercent;
            $sprint->days_elapsed = $daysElapsed;
            $sprint->days_remaining = $daysRemaining;
            $sprint->save();
        }
    }

    /**
     * Get the formatted start date attribute.
     *
     * @return string
     */
    public function getFormattedStartDateAttribute()
    {
        return \App\Helpers\DateHelper::formatSprintDate($this->start_date);
    }
    
    /**
     * Get the formatted end date attribute.
     *
     * @return string
     */
    public function getFormattedEndDateAttribute()
    {
        return \App\Helpers\DateHelper::formatSprintDate($this->end_date);
    }
    
    /**
     * Get the formatted created at attribute.
     *
     * @return string
     */
    public function getFormattedCreatedAtAttribute()
    {
        return \App\Helpers\DateHelper::formatDateTime($this->created_at);
    }
    
    /**
     * Get the formatted updated at attribute.
     *
     * @return string
     */
    public function getFormattedUpdatedAtAttribute()
    {
        return \App\Helpers\DateHelper::formatDateTime($this->updated_at);
    }
} 