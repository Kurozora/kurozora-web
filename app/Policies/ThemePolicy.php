<?php

namespace App\Policies;

use App\Models\Theme;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ThemePolicy
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
     * @param Theme $theme
     * @return Response|bool
     */
    public function view(User $user, Theme $theme): Response|bool
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
        return $user->can('createTheme');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Theme $theme
     * @return Response|bool
     */
    public function update(User $user, Theme $theme): Response|bool
    {
        return $user->can('updateTheme');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Theme $theme
     * @return Response|bool
     */
    public function delete(User $user, Theme $theme): Response|bool
    {
        return $user->can('deleteTheme');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Theme $theme
     * @return Response|bool
     */
    public function restore(User $user, Theme $theme): Response|bool
    {
        return $user->can('restoreTheme');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Theme $theme
     * @return Response|bool
     */
    public function forceDelete(User $user, Theme $theme): Response|bool
    {
        return $user->can('forceDeleteTheme');
    }
}
