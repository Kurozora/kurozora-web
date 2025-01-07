<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can follow another user.
     *
     * @param User $user
     * @param User $model
     *
     * @return bool
     */
    public function follow(User $user, User $model): bool
    {
        return $user->id !== $model->id;
    }

    /**
     * Determine whether the user can view another user's settings.
     *
     * @param User $user
     * @param User $model
     *
     * @return bool
     */
    public function view_settings(User $user, User $model): bool
    {
        return $user->id === $model->parent_id;
    }

//    /**
//     * Determine whether the user can view any models.
//     *
//     * @param User $user
//     *
//     * @return Response|bool
//     */
//    public function viewAny(User $user): Response|bool
//    {
//        return true;
//    }

    /**
     * Determine whether the user can view the model.
     *
     * @param ?User $user
     * @param User $model
     *
     * @return Response|bool
     */
    public function view(?User $user, User $model): Response|bool
    {
        return $user === null || !$model->hasBlocked($user);
    }

//    /**
//     * Determine whether the user can create models.
//     *
//     * @param User $user
//     *
//     * @return Response|bool
//     */
//    public function create(User $user): Response|bool
//    {
//        return $user->can('createUser');
//    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param User $model
     *
     * @return Response|bool
     */
    public function update(User $user, User $model): Response|bool
    {
        return $user->can('updateUser') || $user->id === $model->id;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param User $model
     *
     * @return Response|bool
     */
    public function delete(User $user, User $model): Response|bool
    {
        return $user->can('deleteUser') || $user->id === $model->id;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param User $model
     *
     * @return Response|bool
     */
    public function restore(User $user, User $model): Response|bool
    {
        return $user->can('restoreUser') || $user->id === $model->id;
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param User $model
     *
     * @return Response|bool
     */
    public function forceDelete(User $user, User $model): Response|bool
    {
        return $user->can('forceDeleteUser');
    }
}
