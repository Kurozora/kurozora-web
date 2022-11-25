<?php

namespace App\Policies;

//use App\Models\User;
//use App\Models\UserReputation;
use Illuminate\Auth\Access\HandlesAuthorization;
//use Illuminate\Auth\Access\Response;

class UserReputationPolicy
{
    use HandlesAuthorization;

//    /**
//     * Determine whether the user can view any models.
//     *
//     * @param User $user
//     * @return Response|bool
//     */
//    public function viewAny(User $user): Response|bool
//    {
//        return true;
//    }
//
//    /**
//     * Determine whether the user can view the model.
//     *
//     * @param User $user
//     * @param UserReputation $userReputation
//     * @return Response|bool
//     */
//    public function view(User $user, UserReputation $userReputation): Response|bool
//    {
//        return true;
//    }
//
//    /**
//     * Determine whether the user can create models.
//     *
//     * @param User $user
//     * @return Response|bool
//     */
//    public function create(User $user): Response|bool
//    {
//        return $user->can('createUserReputation');
//    }
//
//    /**
//     * Determine whether the user can update the model.
//     *
//     * @param User $user
//     * @param UserReputation $userReputation
//     * @return Response|bool
//     */
//    public function update(User $user, UserReputation $userReputation): Response|bool
//    {
//        return $user->can('updateUserReputation');
//    }
//
//    /**
//     * Determine whether the user can delete the model.
//     *
//     * @param User $user
//     * @param UserReputation $userReputation
//     * @return Response|bool
//     */
//    public function delete(User $user, UserReputation $userReputation): Response|bool
//    {
//        return $user->can('deleteUserReputation');
//    }
//
//    /**
//     * Determine whether the user can restore the model.
//     *
//     * @param User $user
//     * @param UserReputation $userReputation
//     * @return Response|bool
//     */
//    public function restore(User $user, UserReputation $userReputation): Response|bool
//    {
//        return $user->can('restoreUserReputation');
//    }
//
//    /**
//     * Determine whether the user can permanently delete the model.
//     *
//     * @param User $user
//     * @param UserReputation $userReputation
//     * @return Response|bool
//     */
//    public function forceDelete(User $user, UserReputation $userReputation): Response|bool
//    {
//        return $user->can('forceDeleteUserReputation');
//    }
}
