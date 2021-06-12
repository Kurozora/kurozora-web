<?php

namespace App\Policies;

use App\Models\Episode;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EpisodePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can mark the episode as watched.
     *
     * @param User $user
     * @param Episode $episode
     * @return bool
     */
    public function mark_as_watched(User $user, Episode $episode): bool
    {
        return $user->isTracking($episode->season->anime);
    }
}
