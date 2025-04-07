<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TrelloBoardData extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'board_id',
        'board_name',
        'story_points',
        'cards_by_list',
        'member_points',
        'board_details',
        'backlog_data',
        'last_fetched_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'story_points' => 'array',
        'cards_by_list' => 'array',
        'member_points' => 'array',
        'board_details' => 'array',
        'backlog_data' => 'array',
        'last_fetched_at' => 'datetime',
    ];

    /**
     * Check if the data is stale (older than 24 hours)
     *
     * @return bool
     */
    public function isStale(): bool
    {
        return $this->last_fetched_at->lt(now()->subHours(24));
    }

    /**
     * Format the last fetched time in a human-readable format
     *
     * @return string
     */
    public function getLastFetchedFormatted(): string
    {
        return $this->last_fetched_at->diffForHumans();
    }
}
