<?php

namespace App\Policies;

use App\Models\ExploreCategoryItem;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ExploreCategoryItemPolicy
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
     * @param ExploreCategoryItem $exploreCategoryItem
     * @return Response|bool
     */
    public function view(User $user, ExploreCategoryItem $exploreCategoryItem): Response|bool
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
        return $user->can('createExploreCategoryItem');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param ExploreCategoryItem $exploreCategoryItem
     * @return Response|bool
     */
    public function update(User $user, ExploreCategoryItem $exploreCategoryItem): Response|bool
    {
        return $user->can('updateExploreCategoryItem');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param ExploreCategoryItem $exploreCategoryItem
     * @return Response|bool
     */
    public function delete(User $user, ExploreCategoryItem $exploreCategoryItem): Response|bool
    {
        return $user->can('deleteExploreCategoryItem');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param ExploreCategoryItem $exploreCategoryItem
     * @return Response|bool
     */
    public function restore(User $user, ExploreCategoryItem $exploreCategoryItem): Response|bool
    {
        return $user->can('restoreExploreCategoryItem');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param ExploreCategoryItem $exploreCategoryItem
     * @return Response|bool
     */
    public function forceDelete(User $user, ExploreCategoryItem $exploreCategoryItem): Response|bool
    {
        return $user->can('forceDeleteExploreCategoryItem');
    }
}
