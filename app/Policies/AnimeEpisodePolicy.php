<?php

namespace App\Policies;

use App\Models\AnimeEpisode;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AnimeEpisodePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can mark the episode as watched.
     *
     * @param User $user
     * @param AnimeEpisode $episode
     * @return bool
     */
    public function mark_as_watched(User $user, AnimeEpisode $episode): bool
    {
        return $user->isTracking($episode->season->anime);
    }
}
