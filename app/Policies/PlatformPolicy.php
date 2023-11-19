<?php

namespace App\Policies;

use App\Models\Platform;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PlatformPolicy
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
     * @param Platform $platform
     * @return Response|bool
     */
    public function view(User $user, Platform $platform): Response|bool
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
        return $user->can('createPlatform');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Platform $platform
     * @return Response|bool
     */
    public function update(User $user, Platform $platform): Response|bool
    {
        return $user->can('updatePlatform');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Platform $platform
     * @return Response|bool
     */
    public function delete(User $user, Platform $platform): Response|bool
    {
        return $user->can('deletePlatform');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Platform $platform
     * @return Response|bool
     */
    public function restore(User $user, Platform $platform): Response|bool
    {
        return $user->can('restorePlatform');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Platform $platform
     * @return Response|bool
     */
    public function forceDelete(User $user, Platform $platform): Response|bool
    {
        return $user->can('forceDeletePlatform');
    }
}
