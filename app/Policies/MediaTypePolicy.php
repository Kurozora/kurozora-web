<?php

namespace App\Policies;

use App\Models\MediaType;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MediaTypePolicy
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
     * @param MediaType $mediaType
     * @return Response|bool
     */
    public function view(User $user, MediaType $mediaType): Response|bool
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
        return $user->can('createMediaType');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param MediaType $mediaType
     * @return Response|bool
     */
    public function update(User $user, MediaType $mediaType): Response|bool
    {
        return $user->can('updateMediaType');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param MediaType $mediaType
     * @return Response|bool
     */
    public function delete(User $user, MediaType $mediaType): Response|bool
    {
        return $user->can('deleteMediaType');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param MediaType $mediaType
     * @return Response|bool
     */
    public function restore(User $user, MediaType $mediaType): Response|bool
    {
        return $user->can('restoreMediaType');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param MediaType $mediaType
     * @return Response|bool
     */
    public function forceDelete(User $user, MediaType $mediaType): Response|bool
    {
        return $user->can('forceDeleteMediaType');
    }
}
