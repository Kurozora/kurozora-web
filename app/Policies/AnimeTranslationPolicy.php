<?php

namespace App\Policies;

use App\Models\AnimeTranslation;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class AnimeTranslationPolicy
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
     * @param AnimeTranslation $animeTranslation
     * @return Response|bool
     */
    public function view(User $user, AnimeTranslation $animeTranslation): Response|bool
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
        return $user->can('createAnimeTranslation');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param AnimeTranslation $animeTranslation
     * @return Response|bool
     */
    public function update(User $user, AnimeTranslation $animeTranslation): Response|bool
    {
        return $user->can('updateAnimeTranslation');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param AnimeTranslation $animeTranslation
     * @return Response|bool
     */
    public function delete(User $user, AnimeTranslation $animeTranslation): Response|bool
    {
        return $user->can('deleteAnimeTranslation');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param AnimeTranslation $animeTranslation
     * @return Response|bool
     */
    public function restore(User $user, AnimeTranslation $animeTranslation): Response|bool
    {
        return $user->can('restoreAnimeTranslation');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param AnimeTranslation $animeTranslation
     * @return Response|bool
     */
    public function forceDelete(User $user, AnimeTranslation $animeTranslation): Response|bool
    {
        return $user->can('forceDeleteAnimeTranslation');
    }
}
