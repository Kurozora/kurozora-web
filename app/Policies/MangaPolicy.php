<?php

namespace App\Policies;

use App\Models\Manga;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class MangaPolicy
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
     * @param Manga $manga
     * @return Response|bool
     */
    public function view(User $user, Manga $manga): Response|bool
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
        return $user->can('createManga');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Manga $manga
     * @return Response|bool
     */
    public function update(User $user, Manga $manga): Response|bool
    {
        return $user->can('updateManga');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Manga $manga
     * @return Response|bool
     */
    public function delete(User $user, Manga $manga): Response|bool
    {
        return $user->can('deleteManga');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Manga $manga
     * @return Response|bool
     */
    public function restore(User $user, Manga $manga): Response|bool
    {
        return $user->can('restoreManga');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Manga $manga
     * @return Response|bool
     */
    public function forceDelete(User $user, Manga $manga): Response|bool
    {
        return $user->can('forceDeleteManga');
    }
}
