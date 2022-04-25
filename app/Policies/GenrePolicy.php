<?php

namespace App\Policies;

use App\Models\Genre;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class GenrePolicy
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
     * @param Genre $genre
     * @return Response|bool
     */
    public function view(User $user, Genre $genre): Response|bool
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
        return $user->can('createGenre');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Genre $genre
     * @return Response|bool
     */
    public function update(User $user, Genre $genre): Response|bool
    {
        return $user->can('updateGenre');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Genre $genre
     * @return Response|bool
     */
    public function delete(User $user, Genre $genre): Response|bool
    {
        return $user->can('deleteGenre');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Genre $genre
     * @return Response|bool
     */
    public function restore(User $user, Genre $genre): Response|bool
    {
        return $user->can('restoreGenre');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Genre $genre
     * @return Response|bool
     */
    public function forceDelete(User $user, Genre $genre): Response|bool
    {
        return $user->can('forceDeleteGenre');
    }
}
