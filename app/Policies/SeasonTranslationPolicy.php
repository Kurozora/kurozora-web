<?php

namespace App\Policies;

use App\Models\SeasonTranslation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class SeasonTranslationPolicy
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
     * @param SeasonTranslation $seasonTranslation
     * @return Response|bool
     */
    public function view(User $user, SeasonTranslation $seasonTranslation): Response|bool
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
        return $user->can('createSeasonTranslation');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param SeasonTranslation $seasonTranslation
     * @return Response|bool
     */
    public function update(User $user, SeasonTranslation $seasonTranslation): Response|bool
    {
        return $user->can('updateSeasonTranslation');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param SeasonTranslation $seasonTranslation
     * @return Response|bool
     */
    public function delete(User $user, SeasonTranslation $seasonTranslation): Response|bool
    {
        return $user->can('deleteSeasonTranslation');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param SeasonTranslation $seasonTranslation
     * @return Response|bool
     */
    public function restore(User $user, SeasonTranslation $seasonTranslation): Response|bool
    {
        return $user->can('restoreSeasonTranslation');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param SeasonTranslation $seasonTranslation
     * @return Response|bool
     */
    public function forceDelete(User $user, SeasonTranslation $seasonTranslation): Response|bool
    {
        return $user->can('forceDeleteSeasonTranslation');
    }
}
