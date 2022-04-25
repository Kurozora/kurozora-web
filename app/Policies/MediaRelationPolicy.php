<?php

namespace App\Policies;

use App\Models\MediaRelation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MediaRelationPolicy
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
     * @param MediaRelation $mediaRelation
     * @return Response|bool
     */
    public function view(User $user, MediaRelation $mediaRelation): Response|bool
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
        return $user->can('createMediaRelation');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param MediaRelation $mediaRelation
     * @return Response|bool
     */
    public function update(User $user, MediaRelation $mediaRelation): Response|bool
    {
        return $user->can('updateMediaRelation');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param MediaRelation $mediaRelation
     * @return Response|bool
     */
    public function delete(User $user, MediaRelation $mediaRelation): Response|bool
    {
        return $user->can('deleteMediaRelation');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param MediaRelation $mediaRelation
     * @return Response|bool
     */
    public function restore(User $user, MediaRelation $mediaRelation): Response|bool
    {
        return $user->can('restoreMediaRelation');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param MediaRelation $mediaRelation
     * @return Response|bool
     */
    public function forceDelete(User $user, MediaRelation $mediaRelation): Response|bool
    {
        return $user->can('forceDeleteMediaRelation');
    }
}
