<?php

namespace App\Policies;

use App\Models\Editorial;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class EditorialPolicy
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
        return $user->can('viewEditorial');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Editorial $editorial
     * @return Response|bool
     */
    public function view(User $user, Editorial $editorial): Response|bool
    {
        return $user->can('viewEditorial');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param User $user
     * @return Response|bool
     */
    public function create(User $user): Response|bool
    {
        return $user->can('createEditorial');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Editorial $editorial
     * @return Response|bool
     */
    public function update(User $user, Editorial $editorial): Response|bool
    {
        return $user->can('updateEditorial');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Editorial $editorial
     * @return Response|bool
     */
    public function delete(User $user, Editorial $editorial): Response|bool
    {
        return $user->can('deleteEditorial');
    }
}
