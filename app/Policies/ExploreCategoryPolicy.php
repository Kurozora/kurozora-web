<?php

namespace App\Policies;

use App\Models\ExploreCategory;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class ExploreCategoryPolicy
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
     * @param ExploreCategory $exploreCategory
     * @return Response|bool
     */
    public function view(User $user, ExploreCategory $exploreCategory): Response|bool
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
        return $user->can('createExploreCategory');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param ExploreCategory $exploreCategory
     * @return Response|bool
     */
    public function update(User $user, ExploreCategory $exploreCategory): Response|bool
    {
        return $user->can('updateExploreCategory');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param ExploreCategory $exploreCategory
     * @return Response|bool
     */
    public function delete(User $user, ExploreCategory $exploreCategory): Response|bool
    {
        return $user->can('deleteExploreCategory');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param ExploreCategory $exploreCategory
     * @return Response|bool
     */
    public function restore(User $user, ExploreCategory $exploreCategory): Response|bool
    {
        return $user->can('restoreExploreCategory');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param ExploreCategory $exploreCategory
     * @return Response|bool
     */
    public function forceDelete(User $user, ExploreCategory $exploreCategory): Response|bool
    {
        return $user->can('forceDeleteExploreCategory');
    }
}
