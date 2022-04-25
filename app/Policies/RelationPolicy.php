<?php

namespace App\Policies;

use App\Models\Relation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class RelationPolicy
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
     * @param Relation $relation
     * @return Response|bool
     */
    public function view(User $user, Relation $relation): Response|bool
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
        return $user->can('createRelation');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Relation $relation
     * @return Response|bool
     */
    public function update(User $user, Relation $relation): Response|bool
    {
        return $user->can('updateRelation');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Relation $relation
     * @return Response|bool
     */
    public function delete(User $user, Relation $relation): Response|bool
    {
        return $user->can('deleteRelation');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Relation $relation
     * @return Response|bool
     */
    public function restore(User $user, Relation $relation): Response|bool
    {
        return $user->can('restoreRelation');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Relation $relation
     * @return Response|bool
     */
    public function forceDelete(User $user, Relation $relation): Response|bool
    {
        return $user->can('forceDeleteRelation');
    }
}
