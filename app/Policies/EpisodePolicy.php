<?php

namespace App\Policies;

use App\Models\Episode;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

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
        return $user->isTracking($episode->anime);
    }

    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function viewAny(User $user): Response|bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Episode $episode
     * @return Response|bool
     */
    public function view(User $user, Episode $episode): Response|bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user): Response|bool
    {
        return $user->can('createEpisode');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Episode $episode
     * @return Response|bool
     */
    public function update(User $user, Episode $episode): Response|bool
    {
        return $user->can('updateEpisode');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Episode $episode
     * @return Response|bool
     */
    public function delete(User $user, Episode $episode): Response|bool
    {
        return $user->can('deleteEpisode');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Episode $episode
     * @return Response|bool
     */
    public function restore(User $user, Episode $episode): Response|bool
    {
        return $user->can('restoreEpisode');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Episode $episode
     * @return Response|bool
     */
    public function forceDelete(User $user, Episode $episode): Response|bool
    {
        return $user->can('forceDeleteEpisode');
    }
}
