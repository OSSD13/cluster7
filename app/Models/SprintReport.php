<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\DateHelper;

class SprintReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sprint_id',
        'user_id',
        'board_id',
        'board_name',
        'report_name',
        'notes',
        'story_points_data',
        'bug_cards_data',
        'backlog_data',
        'is_auto_generated',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'story_points_data' => 'json',
        'bug_cards_data' => 'json',
        'backlog_data' => 'json',
        'is_auto_generated' => 'boolean',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'formatted_created_at',
        'formatted_updated_at',
    ];

    /**
     * Get the sprint that owns the report.
     */
    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }

    /**
     * Get the user that owns the report.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Get the number of bugs from the bug cards data.
     */
    public function getBugCountAttribute()
    {
        // Default count if we can't parse the data
        $count = 0;
        
        // Try to extract the bug count from the data
        if (!empty($this->bug_cards_data)) {
            if (is_array($this->bug_cards_data) && isset($this->bug_cards_data['bugCards'])) {
                return count($this->bug_cards_data['bugCards']);
            }
        }
        
        return $count;
    }
    
    /**
     * Get story points data in a structured format
     * 
     * @return array
     */
    public function getStoryPointsStructuredAttribute()
    {
        // Return empty array if no data exists
        if (!$this->story_points_data) {
            return [
                'teamMembers' => [],
                'summary' => [],
                'totals' => [],
            ];
        }
        
        // Handle if the data is already a string (JSON)
        if (is_string($this->story_points_data)) {
            $decoded = json_decode($this->story_points_data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            } else {
                \Log::error('Error decoding story_points_data in SprintReport #' . $this->id . ': ' . json_last_error_msg());
                return [
                    'teamMembers' => [],
                    'summary' => [],
                    'totals' => [],
                ];
            }
        }
        
        return $this->story_points_data;
    }
    
    /**
     * Get bug cards data in a structured format
     * 
     * @return array
     */
    public function getBugCardsStructuredAttribute()
    {
        // Return empty array if no data exists
        if (!$this->bug_cards_data) {
            return [
                'bugCards' => [],
                'bugCount' => '0 bugs',
                'totalBugPoints' => 0,
            ];
        }
        
        // Handle if the data is already a string (JSON)
        if (is_string($this->bug_cards_data)) {
            $decoded = json_decode($this->bug_cards_data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            } else {
                \Log::error('Error decoding bug_cards_data in SprintReport #' . $this->id . ': ' . json_last_error_msg());
                return [
                    'bugCards' => [],
                    'bugCount' => '0 bugs',
                    'totalBugPoints' => 0,
                ];
            }
        }
        
        return $this->bug_cards_data;
    }
    
    /**
     * Get backlog data in a structured format
     * 
     * @return array
     */
    public function getBacklogStructuredAttribute()
    {
        // Return empty array if no data exists
        if (!$this->backlog_data) {
            return [
                'bugCards' => [],
                'bugCount' => '0 bugs',
                'totalBugPoints' => 0,
            ];
        }
        
        return $this->backlog_data;
    }
    
    /**
     * Get the formatted created at attribute.
     *
     * @return string
     */
    public function getFormattedCreatedAtAttribute()
    {
        return DateHelper::formatDateTime($this->created_at);
    }
    
    /**
     * Get the formatted updated at attribute.
     *
     * @return string
     */
    public function getFormattedUpdatedAtAttribute()
    {
        return DateHelper::formatDateTime($this->updated_at);
    }
} 