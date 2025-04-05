<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedReport extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'sprint_id',
        'name',
        'report_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'report_data' => 'json',
    ];

    /**
     * Get the user that owns the report.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sprint that owns the report.
     */
    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }
    
    /**
     * Get the number of bugs from the bug cards data.
     */
    public function getBugCountAttribute()
    {
        // Default count if we can't parse the data
        $count = 0;
        
        // Try to extract the bug count from the data
        if (!empty($this->report_data)) {
            // First check if this is JSON data with the new structure
            if (is_array($this->report_data) && isset($this->report_data['bugCards'])) {
                return count($this->report_data['bugCards']);
            }
            
            // For backwards compatibility with old HTML format
            // Look for the bug count in the HTML
            if (is_string($this->report_data) && preg_match('/<span[^>]*id="bug-count"[^>]*>(.*?)<\/span>/', $this->report_data, $matches)) {
                $count = intval(preg_replace('/[^0-9]/', '', $matches[1]));
            }
            
            // If still zero, count the bug cards
            if ($count === 0 && is_string($this->report_data) && preg_match_all('/<div[^>]*class="bug-card[^>]*>/', $this->report_data, $matches)) {
                $count = count($matches[0]);
            }
        }
        
        return $count;
    }
    
    /**
     * Get structured story points data
     * 
     * @return array
     */
    public function getStoryPointsStructuredAttribute()
    {
        if (empty($this->report_data)) {
            return [
                'summary' => [],
                'teamMembers' => [],
                'totals' => []
            ];
        }
        
        // If already an array (due to casting), return it
        if (is_array($this->report_data)) {
            return $this->report_data;
        }
        
        // Try to decode if it's a JSON string
        if (is_string($this->report_data)) {
            try {
                return json_decode($this->report_data, true) ?: [
                    'summary' => [],
                    'teamMembers' => [],
                    'totals' => []
                ];
            } catch (\Exception $e) {
                return [
                    'summary' => [],
                    'teamMembers' => [],
                    'totals' => []
                ];
            }
        }
        
        return [
            'summary' => [],
            'teamMembers' => [],
            'totals' => []
        ];
    }
    
    /**
     * Get structured bug cards data
     * 
     * @return array
     */
    public function getBugCardsStructuredAttribute()
    {
        if (empty($this->report_data)) {
            return [
                'bugCards' => [],
                'bugCount' => '0 bugs',
                'totalBugPoints' => 0
            ];
        }
        
        // If already an array (due to casting), return it
        if (is_array($this->report_data)) {
            return $this->report_data;
        }
        
        // Try to decode if it's a JSON string
        if (is_string($this->report_data)) {
            try {
                return json_decode($this->report_data, true) ?: [
                    'bugCards' => [],
                    'bugCount' => '0 bugs',
                    'totalBugPoints' => 0
                ];
            } catch (\Exception $e) {
                return [
                    'bugCards' => [],
                    'bugCount' => '0 bugs',
                    'totalBugPoints' => 0
                ];
            }
        }
        
        return [
            'bugCards' => [],
            'bugCount' => '0 bugs',
            'totalBugPoints' => 0
        ];
    }
}
