<?php

namespace App\Policies;

use App\Models\Season;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SeasonPolicy
{
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
     * @param Season $season
     * @return Response|bool
     */
    public function view(User $user, Season $season): Response|bool
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
        return $user->can('createSeason');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Season $season
     * @return Response|bool
     */
    public function update(User $user, Season $season): Response|bool
    {
        return $user->can('updateSeason');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Season $season
     * @return Response|bool
     */
    public function delete(User $user, Season $season): Response|bool
    {
        return $user->can('deleteSeason');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Season $season
     * @return Response|bool
     */
    public function restore(User $user, Season $season): Response|bool
    {
        return $user->can('restoreSeason');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Season $season
     * @return Response|bool
     */
    public function forceDelete(User $user, Season $season): Response|bool
    {
        return $user->can('forceDeleteSeason');
    }
}
