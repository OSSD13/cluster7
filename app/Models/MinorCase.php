<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class MinorCase extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'board_id',
        'sprint',
        'card',
        'description',
        'member',
        'points',
        'user_id',
    ];
    
    protected $table = 'minor_cases';

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'points' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the minor case.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get minor cases for a specific board.
     *
     * @param string $boardId
     * @param int|null $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getByBoard(string $boardId, ?int $userId = null): \Illuminate\Database\Eloquent\Collection
    {
        $query = self::where('board_id', $boardId);

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }
}
