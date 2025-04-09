<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\MinorCase;
use App\Models\User;
use App\Models\Sprint;

final class MinorCasePolicy
{
    /**
     * Determine if the user can view the minor case.
     *
     * @param User $user
     * @param MinorCase $minorCase
     * @return bool
     */
    public function view(User $user, MinorCase $minorCase): bool
    {
        // Users can view minor cases they created
        return $user->id === $minorCase->user_id;
    }

    /**
     * Determine if the user can update the minor case.
     *
     * @param User $user
     * @param MinorCase $minorCase
     * @return bool
     */
    public function update(User $user, MinorCase $minorCase): bool
    {
        // Check if the user owns the minor case
        if ($user->id !== $minorCase->user_id) {
            return false;
        }

        // Get the current sprint
        $currentSprint = Sprint::getCurrentSprint();
        if (!$currentSprint) {
            return false;
        }

        // Only allow updates if the minor case belongs to the current sprint
        return $minorCase->sprint === $currentSprint->sprint_number;
    }

    /**
     * Determine if the user can delete the minor case.
     *
     * @param User $user
     * @param MinorCase $minorCase
     * @return bool
     */
    public function delete(User $user, MinorCase $minorCase): bool
    {
        // Check if the user owns the minor case
        if ($user->id !== $minorCase->user_id) {
            return false;
        }

        // Get the current sprint
        $currentSprint = Sprint::getCurrentSprint();
        if (!$currentSprint) {
            return false;
        }

        // Only allow deletion if the minor case belongs to the current sprint
        return $minorCase->sprint === $currentSprint->sprint_number;
    }
} 