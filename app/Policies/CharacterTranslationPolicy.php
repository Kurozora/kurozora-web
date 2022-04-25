<?php

namespace App\Policies;

use App\Models\CharacterTranslation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CharacterTranslationPolicy
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
     * @param CharacterTranslation $characterTranslation
     * @return Response|bool
     */
    public function view(User $user, CharacterTranslation $characterTranslation): Response|bool
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
        return $user->can('createCharacterTranslation');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param CharacterTranslation $characterTranslation
     * @return Response|bool
     */
    public function update(User $user, CharacterTranslation $characterTranslation): Response|bool
    {
        return $user->can('updateCharacterTranslation');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param CharacterTranslation $characterTranslation
     * @return Response|bool
     */
    public function delete(User $user, CharacterTranslation $characterTranslation): Response|bool
    {
        return $user->can('deleteCharacterTranslation');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param CharacterTranslation $characterTranslation
     * @return Response|bool
     */
    public function restore(User $user, CharacterTranslation $characterTranslation): Response|bool
    {
        return $user->can('restoreCharacterTranslation');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param CharacterTranslation $characterTranslation
     * @return Response|bool
     */
    public function forceDelete(User $user, CharacterTranslation $characterTranslation): Response|bool
    {
        return $user->can('forceDeleteCharacterTranslation');
    }
}
