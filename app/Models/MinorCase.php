<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinorCase extends Model
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
        'title',
        'description',
        'member_name',
        'status',
        'points',
        'additional_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'additional_data' => 'json',
        'points' => 'float',
    ];

    /**
     * Get the sprint that owns the minor case.
     */
    public function sprint()
    {
        return $this->belongsTo(Sprint::class);
    }

    /**
     * Get the user that owns the minor case.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include minor cases for a specific sprint.
     */
    public function scopeBySprint($query, $sprintId)
    {
        return $query->where('sprint_id', $sprintId);
    }

    /**
     * Scope a query to only include minor cases for a specific board.
     */
    public function scopeByBoard($query, $boardId)
    {
        return $query->where('board_id', $boardId);
    }

    /**
     * Scope a query to only include open minor cases.
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope a query to only include resolved minor cases.
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }
}
