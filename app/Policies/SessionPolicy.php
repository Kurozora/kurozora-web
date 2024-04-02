<?php

namespace App\Policies;

use App\Models\Session;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SessionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can get the details of a session
     *
     * @param User $user
     * @param Session $session
     * @return bool
     */
    public function view(User $user, Session $session): bool
    {
        return $user->id === (int) $session->user_id;
    }

    /**
     * Determine whether the user can update a session
     *
     * @param User $user
     * @param Session $session
     * @return bool
     */
    public function update(User $user, Session $session): bool
    {
        return $user->id === (int) $session->user_id;
    }

    /**
     * Determine whether the user can validate a session
     *
     * @param User $user
     * @param Session $session
     * @return bool
     */
    public function validate_session(User $user, Session $session): bool
    {
        return $user->id === (int) $session->user_id;
    }

    /**
     * Determine whether the user can delete a session
     *
     * @param User $user
     * @param Session $session
     * @return bool
     */
    public function delete(User $user, Session $session): bool
    {
        return $user->id === (int) $session->user_id;
    }
}
