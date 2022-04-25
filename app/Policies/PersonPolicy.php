<?php

namespace App\Policies;

use App\Models\Person;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class PersonPolicy
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
     * @param Person $person
     * @return Response|bool
     */
    public function view(User $user, Person $person): Response|bool
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
        return $user->can('createPerson');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Person $person
     * @return Response|bool
     */
    public function update(User $user, Person $person): Response|bool
    {
        return $user->can('updatePerson');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Person $person
     * @return Response|bool
     */
    public function delete(User $user, Person $person): Response|bool
    {
        return $user->can('deletePerson');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Person $person
     * @return Response|bool
     */
    public function restore(User $user, Person $person): Response|bool
    {
        return $user->can('restorePerson');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Person $person
     * @return Response|bool
     */
    public function forceDelete(User $user, Person $person): Response|bool
    {
        return $user->can('forceDeletePerson');
    }
}
