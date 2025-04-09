<?php

namespace App\Policies;

use App\Models\FeedMessage;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class FeedMessagePolicy
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
     * @param FeedMessage $feedMessage
     * @return Response|bool
     */
    public function view(User $user, FeedMessage $feedMessage): Response|bool
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
        return true;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param FeedMessage $feedMessage
     * @return Response|bool
     */
    public function update(User $user, FeedMessage $feedMessage): Response|bool
    {
        return $user->can('updateFeedMessage') || $user->id === $feedMessage->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param FeedMessage $feedMessage
     * @return Response|bool
     */
    public function delete(User $user, FeedMessage $feedMessage): Response|bool
    {
        return $user->can('deleteFeedMessage') || $user->id === $feedMessage->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param FeedMessage $feedMessage
     * @return Response|bool
     */
    public function restore(User $user, FeedMessage $feedMessage): Response|bool
    {
        return $user->can('restoreFeedMessage');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param FeedMessage $feedMessage
     * @return Response|bool
     */
    public function forceDelete(User $user, FeedMessage $feedMessage): Response|bool
    {
        return $user->can('forceDeleteFeedMessage');
    }
}
