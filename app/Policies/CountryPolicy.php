<?php

namespace App\Policies;

use App\Models\Country;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class CountryPolicy
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
     * @param Country $country
     * @return Response|bool
     */
    public function view(User $user, Country $country): Response|bool
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
        return $user->can('createCountry');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Country $country
     * @return Response|bool
     */
    public function update(User $user, Country $country): Response|bool
    {
        return $user->can('updateCountry');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Country $country
     * @return Response|bool
     */
    public function delete(User $user, Country $country): Response|bool
    {
        return $user->can('deleteCountry');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param User $user
     * @param Country $country
     * @return Response|bool
     */
    public function restore(User $user, Country $country): Response|bool
    {
        return $user->can('restoreCountry');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param User $user
     * @param Country $country
     * @return Response|bool
     */
    public function forceDelete(User $user, Country $country): Response|bool
    {
        return $user->can('forceDeleteCountry');
    }
}
