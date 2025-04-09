<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MinorCase;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class MinorCaseService
{
    /**
     * Get all minor cases for a specific board and user.
     *
     * @param string $boardId
     * @param int|null $userId
     * @return Collection
     */
    public function getByBoard(string $boardId, ?int $userId = null): Collection
    {
        return MinorCase::getByBoard($boardId, $userId);
    }

    /**
     * Create a new minor case.
     *
     * @param array<string, mixed> $data
     * @return MinorCase
     */
    public function create(array $data): MinorCase
    {
        return MinorCase::create($data);
    }

    /**
     * Update an existing minor case.
     *
     * @param int $id
     * @param array<string, mixed> $data
     * @param int|null $userId
     * @return MinorCase
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data, ?int $userId = null): MinorCase
    {
        $query = MinorCase::where('id', $id);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $minorCase = $query->firstOrFail();
        $minorCase->update($data);
        
        return $minorCase->fresh();
    }

    /**
     * Delete a minor case.
     *
     * @param int $id
     * @param int|null $userId
     * @return bool
     * @throws ModelNotFoundException
     */
    public function delete(int $id, ?int $userId = null): bool
    {
        $query = MinorCase::where('id', $id);
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        $minorCase = $query->firstOrFail();
        
        return $minorCase->delete();
    }
} 