<?php

namespace App\Policies;

use App\Models\User;
use App\Models\View;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ViewPolicy
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
     * @param View $view
     * @return Response|bool
     */
    public function view(User $user, View $view): Response|bool
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
        return $user->can('createViewStat');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param View $view
     * @return Response|bool
     */
    public function update(User $user, View $view): Response|bool
    {
        return $user->can('updateViewStat');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param View $view
     * @return Response|bool
     */
    public function delete(User $user, View $view): Response|bool
    {
        return $user->can('deleteViewStat');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param View $view
     * @return Response|bool
     */
    public function restore(User $user, View $view): Response|bool
    {
        return $user->can('restoreViewStat');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param View $view
     * @return Response|bool
     */
    public function forceDelete(User $user, View $view): Response|bool
    {
        return $user->can('forceDeleteViewStat');
    }
}
