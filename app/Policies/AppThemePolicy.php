<?php

namespace App\Policies;

use App\Models\AppTheme;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class AppThemePolicy
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
     * @param AppTheme $appTheme
     * @return Response|bool
     */
    public function view(User $user, AppTheme $appTheme): Response|bool
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
        return $user->can('createAppTheme');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param AppTheme $appTheme
     * @return Response|bool
     */
    public function update(User $user, AppTheme $appTheme): Response|bool
    {
        return $user->can('updateAppTheme');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param AppTheme $appTheme
     * @return Response|bool
     */
    public function delete(User $user, AppTheme $appTheme): Response|bool
    {
        return $user->can('deleteAppTheme');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param AppTheme $appTheme
     * @return Response|bool
     */
    public function restore(User $user, AppTheme $appTheme): Response|bool
    {
        return $user->can('restoreAppTheme');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param AppTheme $appTheme
     * @return Response|bool
     */
    public function forceDelete(User $user, AppTheme $appTheme): Response|bool
    {
        return $user->can('forceDeleteAppTheme');
    }
}
