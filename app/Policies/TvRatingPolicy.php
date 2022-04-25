<?php

namespace App\Policies;

use App\Models\TvRating;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class TvRatingPolicy
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
     * @param TvRating $tvRating
     * @return Response|bool
     */
    public function view(User $user, TvRating $tvRating): Response|bool
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
        return $user->can('createTvRating');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param TvRating $tvRating
     * @return Response|bool
     */
    public function update(User $user, TvRating $tvRating): Response|bool
    {
        return $user->can('updateTvRating');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param TvRating $tvRating
     * @return Response|bool
     */
    public function delete(User $user, TvRating $tvRating): Response|bool
    {
        return $user->can('deleteTvRating');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param TvRating $tvRating
     * @return Response|bool
     */
    public function restore(User $user, TvRating $tvRating): Response|bool
    {
        return $user->can('restoreTvRating');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param TvRating $tvRating
     * @return Response|bool
     */
    public function forceDelete(User $user, TvRating $tvRating): Response|bool
    {
        return $user->can('forceDeleteTvRating');
    }
}
