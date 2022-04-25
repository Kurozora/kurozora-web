<?php

namespace App\Policies;

use App\Models\Character;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CharacterPolicy
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
     * @param Character $character
     * @return Response|bool
     */
    public function view(User $user, Character $character): Response|bool
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
        return $user->can('createCharacter');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Character $character
     * @return Response|bool
     */
    public function update(User $user, Character $character): Response|bool
    {
        return $user->can('updateCharacter');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Character $character
     * @return Response|bool
     */
    public function delete(User $user, Character $character): Response|bool
    {
        return $user->can('deleteCharacter');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Character $character
     * @return Response|bool
     */
    public function restore(User $user, Character $character): Response|bool
    {
        return $user->can('restoreCharacter');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Character $character
     * @return Response|bool
     */
    public function forceDelete(User $user, Character $character): Response|bool
    {
        return $user->can('forceDeleteCharacter');
    }
}
