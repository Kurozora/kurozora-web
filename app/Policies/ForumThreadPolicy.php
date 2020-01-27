<?php

namespace App\Policies;

use App\ForumThread;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ForumThreadPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can lock a thread
     *
     * @param User $user
     * @param ForumThread $thread
     * @return bool
     */
    function lock_thread(User $user, ForumThread $thread) {
        return $user->hasRole('admin');
    }
}
