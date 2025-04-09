<?php

namespace App\Policies;

use App\Models\MediaRating;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MediaRatingPolicy
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
     * @param MediaRating $mediaRating
     * @return Response|bool
     */
    public function view(User $user, MediaRating $mediaRating): Response|bool
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
        return $user->can('createMediaRating');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param MediaRating $mediaRating
     * @return Response|bool
     */
    public function update(User $user, MediaRating $mediaRating): Response|bool
    {
        return $user->can('updateMediaRating') || $user->id === $mediaRating->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param MediaRating $mediaRating
     * @return Response|bool
     */
    public function delete(User $user, MediaRating $mediaRating): Response|bool
    {
        return $user->can('deleteMediaRating') || $user->id === $mediaRating->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param MediaRating $mediaRating
     * @return Response|bool
     */
    public function restore(User $user, MediaRating $mediaRating): Response|bool
    {
        return $user->can('restoreMediaRating');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param MediaRating $mediaRating
     * @return Response|bool
     */
    public function forceDelete(User $user, MediaRating $mediaRating): Response|bool
    {
        return $user->can('forceDeleteMediaRating');
    }
}
