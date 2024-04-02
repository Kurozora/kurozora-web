<?php

namespace App\Policies;

use App\Models\PersonalAccessToken;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PersonalAccessTokenPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can get the details of an access token
     *
     * @param User $user
     * @param PersonalAccessToken $accessToken
     * @return bool
     */
    public function view(User $user, PersonalAccessToken $accessToken): bool
    {
        return $user->id === (int) $accessToken->tokenable_id
            && $accessToken->tokenable_type == User::class;
    }

    /**
     * Determine whether the user can update an access token
     *
     * @param User $user
     * @param PersonalAccessToken $accessToken
     * @return bool
     */
    public function update(User $user, PersonalAccessToken $accessToken): bool
    {
        return $user->id === (int) $accessToken->tokenable_id
            && $accessToken->tokenable_type == User::class;
    }

    /**
     * Determine whether the user can validate an access token
     *
     * @param User $user
     * @param PersonalAccessToken $accessToken
     * @return bool
     */
    public function validate_accessToken(User $user, PersonalAccessToken $accessToken): bool
    {
        return $user->id === (int) $accessToken->tokenable_id
            && $accessToken->tokenable_type == User::class;
    }

    /**
     * Determine whether the user can delete an access token
     *
     * @param User $user
     * @param PersonalAccessToken $accessToken
     * @return bool
     */
    public function delete(User $user, PersonalAccessToken $accessToken): bool
    {
        return $user->id === (int) $accessToken->tokenable_id
            && $accessToken->tokenable_type == User::class;
    }
}
